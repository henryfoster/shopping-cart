<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CartItemDto
{
    public function __construct(
        #[Assert\GreaterThan(0)]
        public int $productId,
        #[Assert\GreaterThan(0)]
        public int $amount,
    ) {
    }
}
