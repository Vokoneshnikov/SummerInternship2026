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

    public function getProducts(string $query, int $limit = 20, float $threshold = 0.0): array
    {
        //TODO ПОИГРАТЬ С ЧИСЛАМИ ТАК, ЧТОБЫ ЗАПРОСЫ ВЫВОДИЛИСЬ БОЛЕЕ СТРУКТУРИРОВАННО

        return $this->createQueryBuilder('p')
            ->where('SIMILARITY(p.name, :query) > :threshold')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('threshold', $threshold)
            ->orderBy('SIMILARITY(p.name, :query)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
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
    public function getAllIds() : array
    {
        $products =  $this->findAll();

        return array_map(function($product) {
            return $product->getId();
        }, $products);
    }
    public function delete($id) : void
    {
        $product = $this->find($id);

        $this->getEntityManager()->remove($product);
        $this->getEntityManager()->flush();
    }

    private function createProduct(Product $product): void
    {
        $this->getEntityManager()->persist($product);
        $this->getEntityManager()->flush();
    }

}
