<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Model\CartItemDto;
use App\Model\CartItemEditDto;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final readonly class CartService
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function createNewCart(): Cart
    {
        $cart = new Cart();
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $cart;
    }

    public function removeCartItem(CartItem $cartItem): void
    {
        $this->entityManager->remove($cartItem);
        $this->entityManager->flush();
    }

    public function updateCartItem(CartItem $cartItem, CartItemEditDto $cartItemEditDto): CartItem
    {
        $cartItem->setAmount($cartItemEditDto->amount);
        $this->entityManager->flush();

        return $cartItem;
    }

    public function addCartItemToCart(CartItemDto $cartItemDto, Cart $cart): CartItem
    {
        $product = $this->productRepository->find($cartItemDto->productId);
        if (!$product) {
            $errorMessage = 'Product with id '.$cartItemDto->productId.' not found.';
            $violation = new ConstraintViolation(
                message: $errorMessage,
                messageTemplate: null,
                parameters: [],
                root: $cartItemDto->productId,
                propertyPath: 'productId',
                invalidValue: $cartItemDto->productId,
            );
            $violations = new ConstraintViolationList([$violation]);
            throw new ValidationFailedException($cartItemDto, $violations);
        }

        $cartItem = $cart->findItemByProductId($cartItemDto->productId);
        if ($cartItem) {
            $cartItem->setAmount($cartItem->getAmount() + $cartItemDto->amount);
        } else {
            $cartItem = new CartItem(amount: $cartItemDto->amount, cart: $cart, product: $product);
            $cart->addCartItem($cartItem);
        }

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $cartItem;
    }
}
