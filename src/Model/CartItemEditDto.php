<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CartItemEditDto
{
    public function __construct(
        #[Assert\GreaterThan(0)]
        public int $amount,
    ) {
    }
}
