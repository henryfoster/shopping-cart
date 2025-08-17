<?php

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class ProductApiController extends AbstractController
{
    #[OA\Tag(name: 'product', description: 'Just to have a simple way to get product ids in this demo')]
    #[Route('/api/products', name: 'app_api_products_get_item', methods: ['GET'])]
    public function getProducts(
        ProductRepository $productRepository,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_INT, options: ['min_range' => 1])]
        int $page = 1,
    ): Response {
        $products = $productRepository->getProductsPaginated($page);

        return $this->json($products);
    }
}
