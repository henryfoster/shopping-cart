<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return array{items: Product[], totalPages: int, currentPage: int}
     */
    public function getProductsPaginated(int $page = 1, int $limit = 10): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder->getQuery());

        return [
            'items' => iterator_to_array($paginator),
            'totalPages' => (int) ceil($paginator->count() / $limit),
            'currentPage' => $page,
        ];
    }
}
