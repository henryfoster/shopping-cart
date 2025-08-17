<?php

namespace App\Tests\web\Controller\Api;

use App\EventListener\BadRequestExceptionListener;
use App\Factory\CartFactory;
use App\Factory\CartItemFactory;
use App\Factory\ProductFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CartApiControllerAddItemToCartTest extends KernelTestCase
{
    use HasBrowser;
    use Factories;
    use ResetDatabase;

    public function testAddItemToCart(): void
    {
        $product = ProductFactory::createOne(['name' => 'IPhone 6', 'priceInCent' => 60000]);
        $cart = CartFactory::createOne([]);

        $json = $this->browser()
            ->post('/api/carts/'.$cart->getId().'/cart-items', [
                'json' => [
                    'productId' => $product->getId(),
                    'amount' => 2,
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson()
            ->assertJsonMatches('id', 1)
            ->assertJsonMatches('productName', $product->getName())
            ->assertJsonMatches('amount', 2)
            ->assertJsonMatches('productPricePerItemInCent', $product->getPriceInCent())
            ->assertJsonMatches('productPriceTotalInCent', $product->getPriceInCent() * 2)
            ->json()->decoded();

        $this->assertLessThanOrEqual(
            maximum: new \DateTimeImmutable(),
            actual: new \DateTimeImmutable($json['createdAt']),
        );
        $this->assertLessThanOrEqual(
            maximum: new \DateTimeImmutable(),
            actual: new \DateTimeImmutable($json['updatedAt']),
        );
    }

    public function testAddItemToCartWithNonExistingProduct(): void
    {
        $cart = CartFactory::createOne([]);

        $this->browser()
            ->post('/api/carts/'.$cart->getId().'/cart-items', [
                'json' => [
                    'productId' => 999,
                    'amount' => 2,
                ],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('violations[0].property', 'productId')
            ->assertJsonMatches('violations[0].message', 'Product with id 999 not found.')
            ->assertJson()
            ->json()->decoded();

        $this->assertEmpty($cart->getCartItems()->toArray());
    }

    public function testAddItemToCartWithZeroNumbers(): void
    {
        $cart = CartFactory::createOne([]);

        $this->browser()
            ->post('/api/carts/'.$cart->getId().'/cart-items', [
                'json' => [
                    'productId' => 0,
                    'amount' => 0,
                ],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('violations[0].property', 'productId')
            ->assertJsonMatches('violations[0].message', 'This value should be greater than 0.')
            ->assertJsonMatches('violations[1].property', 'amount')
            ->assertJsonMatches('violations[1].message', 'This value should be greater than 0.')
            ->assertJson()
            ->json()->decoded();

        $this->assertEmpty($cart->getCartItems()->toArray());
    }

    public function testAddItemToCartWithWrongTypes(): void
    {
        $cart = CartFactory::createOne([]);

        $this->browser()
            ->post('/api/carts/'.$cart->getId().'/cart-items', [
                'json' => [
                    'productId' => 'not a number',
                    'amount' => 'not a number',
                ],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('violations[0].property', 'productId')
            ->assertJsonMatches('violations[0].message', 'This value should be of type int.')
            ->assertJsonMatches('violations[1].property', 'amount')
            ->assertJsonMatches('violations[1].message', 'This value should be of type int.')
            ->assertJson()
            ->json()->decoded();

        $this->assertEmpty($cart->getCartItems()->toArray());
    }

    public function testAddItemToCartWithoutBody(): void
    {
        $cart = CartFactory::createOne([]);

        $this->browser()
            ->post('/api/carts/'.$cart->getId().'/cart-items')
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson()
            ->assertJsonMatches('error', BadRequestExceptionListener::MESSAGE)
            ->json()->decoded();

        $this->assertEmpty($cart->getCartItems()->toArray());
    }

    public function testAddSameItemToCart(): void
    {
        $product = ProductFactory::createOne(['name' => 'IPhone 6', 'priceInCent' => 60000]);
        $cart = CartFactory::createOne([]);
        CartItemFactory::createOne([
            'product' => $product,
            'amount' => 1,
            'cart' => $cart,
        ]);

        $json = $this->browser()
            ->post('/api/carts/'.$cart->getId().'/cart-items', [
                'json' => [
                    'productId' => $product->getId(),
                    'amount' => 2,
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson()
            ->assertJsonMatches('id', 1)
            ->assertJsonMatches('productName', $product->getName())
            ->assertJsonMatches('amount', 3)
            ->assertJsonMatches('productPricePerItemInCent', $product->getPriceInCent())
            ->assertJsonMatches('productPriceTotalInCent', $product->getPriceInCent() * 3)
            ->json()->decoded();

        $this->assertLessThanOrEqual(
            maximum: new \DateTimeImmutable(),
            actual: new \DateTimeImmutable($json['createdAt']),
        );
        $this->assertLessThanOrEqual(
            maximum: new \DateTimeImmutable(),
            actual: new \DateTimeImmutable($json['updatedAt']),
        );
    }

    public function testAddSecondItemToCart(): void
    {
        $product = ProductFactory::createOne(['name' => 'IPhone 6', 'priceInCent' => 60000]);
        $product2 = ProductFactory::createOne(['name' => 'Samsung Galaxy S20', 'priceInCent' => 70000]);
        $cart = CartFactory::createOne([]);
        CartItemFactory::createOne([
            'product' => $product,
            'amount' => 1,
            'cart' => $cart,
        ]);

        $json = $this->browser()
            ->post('/api/carts/'.$cart->getId().'/cart-items', [
                'json' => [
                    'productId' => $product2->getId(),
                    'amount' => 2,
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson()
            ->assertJsonMatches('id', 2)
            ->assertJsonMatches('productName', $product2->getName())
            ->assertJsonMatches('amount', 2)
            ->assertJsonMatches('productPricePerItemInCent', $product2->getPriceInCent())
            ->assertJsonMatches('productPriceTotalInCent', $product2->getPriceInCent() * 2)
            ->json()->decoded();

        $this->assertLessThanOrEqual(
            maximum: new \DateTimeImmutable(),
            actual: new \DateTimeImmutable($json['createdAt']),
        );
        $this->assertLessThanOrEqual(
            maximum: new \DateTimeImmutable(),
            actual: new \DateTimeImmutable($json['updatedAt']),
        );
    }
}
