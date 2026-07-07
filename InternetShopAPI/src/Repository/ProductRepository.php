<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function getProducts(string $query): array
    {
        //находим по полнотекстовому поиску
    }
    public function updateProduct(Product $product): void
    {
        $result = $this->find($product->getId());
        if ($result === null) {
            $this->createProduct($product);
            return;
        }
        $result->setName($product->getName());
        $result->setDescription($product->getDescription());
        $result->setPrice($product->getPrice());
        $result->setCurrency($product->getCurrency());

        $this->getEntityManager()->flush();
    }
    private function createProduct(Product $product): void
    {
        $this->getEntityManager()->persist($product);
        $this->getEntityManager()->flush();
    }

}
