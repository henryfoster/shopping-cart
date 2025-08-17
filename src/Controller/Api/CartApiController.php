<?php

namespace App\Controller\Api;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Model\CartItemDto;
use App\Model\CartItemEditDto;
use App\Service\CartService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'cart', description: 'Cart operations')]
final class CartApiController extends AbstractController
{
    #[Route('/api/carts/{cart}', name: 'app_api_carts_get_item', methods: ['GET'])]
    public function getCart(
        Cart $cart,
    ): Response {
        return $this->json($cart->toArray());
    }

    #[Route('/api/carts', name: 'app_api_carts_post_collection', methods: ['POST'])]
    public function createCart(
        CartService $cartService,
    ): Response {
        $cart = $cartService->createNewCart();

        return $this->json(
            data: $cart->toArray(),
            status: Response::HTTP_CREATED,
        );
    }

    #[OA\RequestBody(content: new Model(type: CartItemDto::class))]
    #[Route('/api/carts/{cart}/cart-items', name: 'app_api_carts_items_post_collection', methods: ['POST'])]
    public function addCartItem(
        Cart $cart,
        #[MapRequestPayload]
        CartItemDto $cartItemDto,
        CartService $cartService,
    ): Response {
        $cartItem = $cartService->addCartItemToCart($cartItemDto, $cart);

        return $this->json(
            data: $cartItem->toArray(),
            status: Response::HTTP_CREATED,
        );
    }

    #[Route('/api/cart-items/{cartItem}', name: 'app_api_items_delete_item', methods: ['DELETE'])]
    public function deleteCartItem(
        CartItem $cartItem,
        CartService $cartService,
    ): Response {
        $cartService->removeCartItem($cartItem);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[OA\RequestBody(content: new Model(type: CartItemEditDto::class))]
    #[Route('/api/cart-items/{cartItem}', name: 'app_api_items_edit_item', methods: ['PUT'])]
    public function editCartItem(
        CartItem $cartItem,
        #[MapRequestPayload]
        CartItemEditDto $cartItemEditDto,
        CartService $cartService,
    ): Response {
        $cartService->updateCartItem($cartItem, $cartItemEditDto);

        return $this->json($cartItem->toArray());
    }
}
