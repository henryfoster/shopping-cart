<?php

namespace App\Tests\web\Controller\Api;

use App\Factory\CartFactory;
use App\Factory\CartItemFactory;
use App\Factory\ProductFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CartApiControllerTest extends KernelTestCase
{
    use HasBrowser;
    use Factories;
    use ResetDatabase;

    public function testCreateCart(): void
    {
        $json = $this->browser()
            ->post('/api/carts')
            ->assertSuccessful()
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson()
            ->assertJsonMatches('id', 1)
            ->assertJsonMatches('cartItems', [])
            ->assertJsonMatches('totalCartPriceInCent', 0)
            ->assertJsonMatches('uniqueItemsInCart', 0)
            ->assertJsonMatches('totalItemsInCart', 0)
            ->json()->decoded();

        $this->assertLessThanOrEqual(
            maximum: new \DateTimeImmutable(),
            actual: new \DateTimeImmutable($json['createdAt']),
        );
    }

    public function testAddItemToCart(): void
    {
        $product = ProductFactory::createOne(['name' => 'IPhone 6', 'priceInCent' => 60000]);

        $cartFactory = CartFactory::createOne([]);

        $json = $this->browser()
            ->post('/api/carts/'.$cartFactory->getId().'/cart-items', [
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

    public function testGetCart(): void
    {
        $product1 = ProductFactory::createOne(['name' => 'IPhone 6', 'priceInCent' => 60000]);
        $product2 = ProductFactory::createOne(['name' => 'Samsung Galaxy S20', 'priceInCent' => 70000]);

        $cart = CartFactory::createOne();
        $cartItem1 = CartItemFactory::createOne([
            'product' => $product1,
            'amount' => 1,
            'cart' => $cart,
        ]);

        $cartItem2 = CartItemFactory::createOne([
            'product' => $product2,
            'amount' => 2,
            'cart' => $cart,
        ]);

        $json = $this->browser()
            ->get('/api/carts/'.$cart->getId())
            ->assertSuccessful()
            ->assertJson()
            ->assertJsonMatches(
                'totalCartPriceInCent',
                $product1->getPriceInCent() + $product2->getPriceInCent() * 2)
            ->assertJsonMatches('uniqueItemsInCart', 2)
            ->assertJsonMatches('totalItemsInCart', 3)
            ->json()->decoded();

        $this->assertSame(expected: 2, actual: count($json['cartItems']));
        // Assert first cart item
        $this->assertSame($cartItem1->getId(), $json['cartItems'][0]['id']);
        $this->assertSame($product1->getName(), $json['cartItems'][0]['productName']);
        $this->assertSame($product1->getPriceInCent(), $json['cartItems'][0]['productPriceTotalInCent']);
        $this->assertSame(1, $json['cartItems'][0]['amount']);

        // Assert second cart item
        $this->assertSame($cartItem2->getId(), $json['cartItems'][1]['id']);
        $this->assertSame($product2->getName(), $json['cartItems'][1]['productName']);
        $this->assertSame($product2->getPriceInCent(), $json['cartItems'][1]['productPricePerItemInCent']);
        $this->assertSame($product2->getPriceInCent() * 2, $json['cartItems'][1]['productPriceTotalInCent']);
        $this->assertSame(2, $json['cartItems'][1]['amount']);

        // Assert timestamps exist for cart items
        $this->assertArrayHasKey('createdAt', $json['cartItems'][0]);
        $this->assertArrayHasKey('updatedAt', $json['cartItems'][0]);
        $this->assertArrayHasKey('createdAt', $json['cartItems'][1]);
        $this->assertArrayHasKey('updatedAt', $json['cartItems'][1]);

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
