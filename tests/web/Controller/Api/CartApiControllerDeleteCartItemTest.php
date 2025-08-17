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

class CartApiControllerDeleteCartItemTest extends KernelTestCase
{
    use HasBrowser;
    use Factories;
    use ResetDatabase;

    public function testDeleteItemFromCart(): void
    {
        $product = ProductFactory::createOne(['name' => 'IPhone 6', 'priceInCent' => 60000]);
        $cart = CartFactory::createOne([]);

        $cartItem = CartItemFactory::createOne([
            'product' => $product,
            'amount' => 1,
            'cart' => $cart,
        ]);

        $this->browser()
            ->delete('/api/cart-items/'.$cartItem->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEmpty(actual: $cart->getCartItems()->toArray());
    }

    public function testTryToDeleteNoneExistingItemFromCart(): void
    {
        $product = ProductFactory::createOne(['name' => 'IPhone 6', 'priceInCent' => 60000]);
        $cart = CartFactory::createOne([]);

        CartItemFactory::createOne([
            'product' => $product,
            'amount' => 1,
            'cart' => $cart,
        ]);

        $this->browser()
            ->delete('/api/cart-items/999')
            ->assertStatus(Response::HTTP_NOT_FOUND);

        $this->assertNotEmpty(actual: $cart->getCartItems()->toArray());
    }
}
