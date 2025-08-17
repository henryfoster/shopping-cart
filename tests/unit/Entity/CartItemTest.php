<?php

namespace App\Tests\unit\Entity;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Tests\ReflectionHelper;
use PHPUnit\Framework\TestCase;

class CartItemTest extends TestCase
{
    public function testToArray(): void
    {
        $cart = new Cart();
        $product = new Product(name: 'iPhone 15', priceInCent: 120000);

        ReflectionHelper::setPrivateProperty($product, 'id', 42);

        $cartItem = new CartItem(amount: 3, cart: $cart, product: $product);
        ReflectionHelper::setPrivateProperty($cartItem, 'id', 1);

        $result = $cartItem->toArray();

        $this->assertSame(1, $result['id']);
        $this->assertSame(42, $result['productId']);
        $this->assertSame('iPhone 15', $result['productName']);
        $this->assertSame(120000, $result['productPricePerItemInCent']);
        $this->assertSame(360000, $result['productPriceTotalInCent']);
        $this->assertSame(3, $result['amount']);
        $this->assertArrayHasKey('createdAt', $result);
        $this->assertArrayHasKey('updatedAt', $result);
    }
}
