<?php

namespace App\Tests\UnitTests\Repository;

use App\Entity\Product;
use App\Enums\Currency;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class ProductRepositoryTest extends TestCase
{
    private ProductRepository $repository;
    private EntityManagerInterface $entityManager;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry
            ->method('getManagerForClass')
            ->with(Product::class)
            ->willReturn($this->entityManager);

        $this->repository = $this->getMockBuilder(ProductRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['find', 'findAll', 'getEntityManager', 'createQueryBuilder'])
            ->getMock();
    }

    /**
     * Создает мок для UuidInterface.
     */
    private function createUuidMock(string $uuidString): UuidInterface
    {
        $uuid = $this->createMock(UuidInterface::class);
        $uuid->method('toString')->willReturn($uuidString);
        $uuid->method('__toString')->willReturn($uuidString);
        return $uuid;
    }

    public function testGetProductsReturnsProducts(): void
    {
        $query = 'test';
        $limit = 20;
        $threshold = 0.1;

        $expectedProducts = [
            $this->createMock(Product::class),
            $this->createMock(Product::class),
        ];

        // Создаем мок Query (не AbstractQuery)
        $queryObject = $this->createMock(Query::class);
        $queryObject->method('getResult')->willReturn($expectedProducts);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($queryObject);

        $this->repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($queryBuilder);

        $result = $this->repository->getProducts($query, $limit, $threshold);

        $this->assertSame($expectedProducts, $result);
        $this->assertCount(2, $result);
    }

    public function testGetProductsWithDefaultParameters(): void
    {
        $query = 'phone';
        $expectedProducts = [$this->createMock(Product::class)];

        $queryObject = $this->createMock(Query::class);
        $queryObject->method('getResult')->willReturn($expectedProducts);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($queryObject);

        $this->repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $result = $this->repository->getProducts($query);

        $this->assertSame($expectedProducts, $result);
    }

    public function testGetProductsReturnsEmptyArray(): void
    {
        $query = 'nonexistent';

        $queryObject = $this->createMock(Query::class);
        $queryObject->method('getResult')->willReturn([]);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('setMaxResults')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($queryObject);

        $this->repository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $result = $this->repository->getProducts($query);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testUpdateProductUpdatesExistingProduct(): void
    {
        $uuidString = '123e4567-e89b-12d3-a456-426614174000';
        $uuid = $this->createUuidMock($uuidString);

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn($uuid);
        $product->method('getName')->willReturn('Updated Name');
        $product->method('getDescription')->willReturn('Updated Description');
        $product->method('getPrice')->willReturn('199.99');
        $product->method('getCurrency')->willReturn(Currency::EUR);

        $existingProduct = $this->createMock(Product::class);
        $existingProduct->expects($this->once())->method('setName')->with('Updated Name');
        $existingProduct->expects($this->once())->method('setDescription')->with('Updated Description');
        $existingProduct->expects($this->once())->method('setPrice')->with('199.99');
        $existingProduct->expects($this->once())->method('setCurrency')->with(Currency::EUR);

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($uuid)
            ->willReturn($existingProduct);

        $this->repository
            ->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->updateProduct($product);
    }

    public function testUpdateProductCreatesNewProductWhenNotExists(): void
    {
        $uuidString = '123e4567-e89b-12d3-a456-426614174000';
        $uuid = $this->createUuidMock($uuidString);

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn($uuid);

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($uuid)
            ->willReturn(null);

        $this->repository
            ->expects($this->exactly(2))
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($product);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->updateProduct($product);
    }

    public function testGetAllIdsReturnsArrayOfIds(): void
    {
        $uuid1 = $this->createUuidMock('id1');
        $uuid2 = $this->createUuidMock('id2');

        $product1 = $this->createMock(Product::class);
        $product1->method('getId')->willReturn($uuid1);

        $product2 = $this->createMock(Product::class);
        $product2->method('getId')->willReturn($uuid2);

        $products = [$product1, $product2];

        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($products);

        $result = $this->repository->getAllIds();

        // getId возвращает UuidInterface, мы ожидаем массив объектов UuidInterface, а не строк
        // Но в реальном коде getAllIds возвращает строки? Смотрим код:
        // return array_map(function($product) { return $product->getId(); }, $products);
        // getId возвращает UuidInterface|null, поэтому массив будет содержать объекты UuidInterface.
        // Однако обычно toString используется для строк. Мы можем проверить, что возвращаются те же объекты.
        $this->assertSame([$uuid1, $uuid2], $result);
    }

    public function testGetAllIdsReturnsEmptyArrayWhenNoProducts(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->repository->getAllIds();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testDeleteRemovesProduct(): void
    {
        $uuidString = '123e4567-e89b-12d3-a456-426614174000';
        $uuid = $this->createUuidMock($uuidString);
        $product = $this->createMock(Product::class);

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($uuid)
            ->willReturn($product);

        // В методе delete вызывается getEntityManager, remove, flush.
        // getEntityManager вызывается дважды: для remove и для flush.
        $this->repository
            ->expects($this->exactly(2))
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($product);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->delete($uuid);
    }
}
