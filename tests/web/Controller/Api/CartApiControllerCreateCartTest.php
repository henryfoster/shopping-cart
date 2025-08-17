<?php

namespace App\Tests\web\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CartApiControllerCreateCartTest extends KernelTestCase
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
}
