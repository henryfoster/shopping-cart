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

class CartApiControllerEditCartItemTest extends KernelTestCase
{
    use HasBrowser;
    use Factories;
    use ResetDatabase;

    public function testEditCartItem(): void
    {
        $product = ProductFactory::createOne(['name' => 'IPhone 6', 'priceInCent' => 60000]);
        $cart = CartFactory::createOne([]);

        $cartItem = CartItemFactory::createOne([
            'product' => $product,
            'amount' => 1,
            'cart' => $cart,
        ]);

        $this->browser()
            ->put('/api/cart-items/'.$cartItem->getId(), [
                'json' => [
                    'amount' => 5,
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson()
            ->assertJsonMatches('amount', 5)
            ->assertJsonMatches('productPriceTotalInCent', $product->getPriceInCent() * 5)
            ->json()->decoded();
    }

    public function testEditNonExistingCartItem(): void
    {
        $product = ProductFactory::createOne(['name' => 'IPhone 6', 'priceInCent' => 60000]);
        $cart = CartFactory::createOne([]);

        CartItemFactory::createOne([
            'product' => $product,
            'amount' => 1,
            'cart' => $cart,
        ]);

        $this->browser()
            ->put('/api/cart-items/999', [
                'json' => [
                    'amount' => 5,
                ],
            ])
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson()
            ->assertJsonMatches('error', 'Not found /api/cart-items/999');
    }
}
