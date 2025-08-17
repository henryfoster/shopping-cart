<?php

namespace App\Controller\Api;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Model\CartItemDto;
use App\Model\CartItemEditDto;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'cart', description: 'Cart operations')]
final class CartApiController extends AbstractController
{
    #[Route('/api/carts/{cart}', name: 'app_api_carts_get', methods: ['GET'])]
    public function getCart(
        Cart $cart,
    ): Response {
        return $this->json($cart->toArray());
    }

    #[Route('/api/carts', name: 'app_api_carts_post', methods: ['POST'])]
    public function createCart(
        EntityManagerInterface $entityManager,
    ): Response {
        $cart = new Cart();
        $entityManager->persist($cart);
        $entityManager->flush();

        return $this->json(
            data: $cart->toArray(),
            status: Response::HTTP_CREATED,
        );
    }

    #[OA\RequestBody(content: new Model(type: CartItemDto::class))]
    #[Route('/api/carts/{cart}/cart-items', name: 'app_carts_items_post', methods: ['POST'])]
    public function addCartItem(
        Cart $cart,
        #[MapRequestPayload]
        CartItemDto $cartItemDto,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $product = $productRepository->find($cartItemDto->productId); // todo: extract into service

        $cartItem = $cart->findItemByProductId($product->getId());
        if ($cartItem) {
            $cartItem->setAmount($cartItem->getAmount() + $cartItemDto->amount);
        } else {
            $cartItem = new CartItem(amount: $cartItemDto->amount, cart: $cart, product: $product);
            $cart->addCartItem($cartItem);
        }

        $entityManager->persist($cart);
        $entityManager->flush();

        return $this->json(
            data: $cartItem->toArray(),
            status: Response::HTTP_CREATED,
        );
    }

    #[Route('/api/cart-items/{cartItem}', name: 'app_items_delete', methods: ['DELETE'])]
    public function deleteCartItem(
        CartItem $cartItem,
        EntityManagerInterface $entityManager,
    ): Response {
        $entityManager->remove($cartItem);
        $entityManager->flush();

        return new Response(status: 204);
    }

    #[OA\RequestBody(content: new Model(type: CartItemEditDto::class))] // todo: add response model for api
    #[Route('/api/cart-items/{cartItem}', name: 'app_items_edit', methods: ['PUT'])] // todo: make named param say cartItemId and expect integer
    public function editCartItem(
        CartItem $cartItem,
        #[MapRequestPayload]
        CartItemEditDto $cartItemEditDto,
        EntityManagerInterface $entityManager,
    ): Response {
        $cartItem->setAmount($cartItemEditDto->amount);
        $entityManager->flush();

        return $this->json($cartItem->toArray()); // todo: make sure all status codes are correct
    }
}
