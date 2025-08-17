<?php

namespace App\Tests\web\Controller\Api;

use App\Factory\CartFactory;
use App\Factory\CartItemFactory;
use App\Factory\ProductFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CartApiControllerGetCartTest extends KernelTestCase
{
    use HasBrowser;
    use Factories;
    use ResetDatabase;

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
            ->assertJsonMatches('cartItems | length(@)', 2)
            // Assert first cart item
            ->assertJsonMatches('cartItems[0].id', $cartItem1->getId())
            ->assertJsonMatches('cartItems[0].productName', $product1->getName())
            ->assertJsonMatches('cartItems[0].productPriceTotalInCent', $product1->getPriceInCent())
            ->assertJsonMatches('cartItems[0].amount', 1)
            // Assert second cart item
            ->assertJsonMatches('cartItems[1].id', $cartItem2->getId())
            ->assertJsonMatches('cartItems[1].productName', $product2->getName())
            ->assertJsonMatches('cartItems[1].productPricePerItemInCent', $product2->getPriceInCent())
            ->assertJsonMatches('cartItems[1].productPriceTotalInCent', $product2->getPriceInCent() * 2)
            ->assertJsonMatches('cartItems[1].amount', 2)
            // Assert timestamps exist for cart items
            ->assertJsonMatches('cartItems[0].createdAt != `null`', true)
            ->assertJsonMatches('cartItems[0].updatedAt != `null`', true)
            ->assertJsonMatches('cartItems[1].createdAt != `null`', true)
            ->assertJsonMatches('cartItems[1].updatedAt != `null`', true)
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
