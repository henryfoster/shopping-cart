<?php

namespace App\Tests\unit\Entity;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Tests\ReflectionHelper;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function testCartCalculationEmptyCart(): void
    {
        $cart = new Cart();

        $this->assertEmpty($cart->getCartItems()->toArray());
        $this->assertSame(expected: 0, actual: $cart->getTotalAmount());
        $this->assertSame(expected: 0, actual: $cart->getTotalPrice());
    }

    public function testCartCalculation(): void
    {
        $cart = new Cart();
        $product = new Product(name: 'Iphone', priceInCent: 9000);
        $product2 = new Product(name: 'Iphone 6', priceInCent: 90000);
        $cartItem = new CartItem(amount: 2, cart: $cart, product: $product);
        $cart->addCartItem($cartItem);

        $this->assertNotEmpty($cart->getCartItems()->toArray());
        $this->assertSame(expected: 2, actual: $cart->getTotalAmount());
        $this->assertSame(expected: 9000 * 2, actual: $cart->getTotalPrice());

        $cartItem2 = new CartItem(amount: 2, cart: $cart, product: $product2);
        $cart->addCartItem($cartItem2);

        $this->assertSame(expected: 4, actual: $cart->getTotalAmount());
        $this->assertSame(expected: 9000 * 2 + 90000 * 2, actual: $cart->getTotalPrice());
    }

    public function testFindCartItemByProductId(): void
    {
        $cart = new Cart();
        $product = new Product(name: 'Iphone', priceInCent: 9000);

        ReflectionHelper::setPrivateProperty($product, 'id', 1);

        $cartItem = new CartItem(amount: 2, cart: $cart, product: $product);
        $cart->addCartItem($cartItem);

        $this->assertSame(expected: $cartItem, actual: $cart->findItemByProductId(1));
    }

    public function testFindCartItemByProductIdNotFound(): void
    {
        $cart = new Cart();
        $product = new Product(name: 'Iphone', priceInCent: 9000);

        ReflectionHelper::setPrivateProperty($product, 'id', 1);

        $cartItem = new CartItem(amount: 2, cart: $cart, product: $product);
        $cart->addCartItem($cartItem);

        $this->assertNull(actual: $cart->findItemByProductId(2));
    }

    public function testToArray(): void
    {
        $cart = new Cart();
        $product1 = new Product(name: 'iPhone', priceInCent: 60000);
        $product2 = new Product(name: 'Samsung', priceInCent: 70000);

        ReflectionHelper::setPrivateProperty($product1, 'id', 1);
        ReflectionHelper::setPrivateProperty($product2, 'id', 2);
        ReflectionHelper::setPrivateProperty($cart, 'id', 123);

        $cartItem1 = new CartItem(amount: 2, cart: $cart, product: $product1);
        $cartItem2 = new CartItem(amount: 1, cart: $cart, product: $product2);
        $cart->addCartItem($cartItem1);
        $cart->addCartItem($cartItem2);

        $result = $cart->toArray();

        $this->assertSame(123, $result['id']);
        $this->assertSame(2, $result['uniqueItemsInCart']);
        $this->assertSame(3, $result['totalItemsInCart']);
        $this->assertSame(190000, $result['totalCartPriceInCent']);
        $this->assertCount(2, $result['cartItems']);
        $this->assertArrayHasKey('createdAt', $result);
        $this->assertArrayHasKey('updatedAt', $result);
    }
}
