<?php

namespace App\Tests\UnitTests;

use App\Entity\ExchangeRates;
use App\Entity\Product;
use App\Enums\Currency;
use App\Repository\ProductRepository;
use App\Service\ExchangeRatesService;
use App\Service\ProductService;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ProductServiceTest extends TestCase
{
    private ProductRepository $productRepository;
    private ExchangeRatesService $exchangeRatesService;
    private ProductService $service;

    // Константы для тестовых UUID
    private const UUID_1 = '123e4567-e89b-12d3-a456-426614174000';
    private const UUID_2 = '123e4567-e89b-12d3-a456-426614174001';
    private const UUID_3 = '123e4567-e89b-12d3-a456-426614174002';

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->exchangeRatesService = $this->createMock(ExchangeRatesService::class);
        $this->service = new ProductService(
            $this->productRepository,
            $this->exchangeRatesService
        );
    }

    /**
     * Вспомогательный метод для подмены пути к файлу (без setAccessible).
     */
    private function setProductFilePath(ProductService $service, string $path): void
    {
        $reflection = new ReflectionProperty($service, 'productFilePath');
        // setAccessible() не нужен в PHP 8.1+
        $reflection->setValue($service, $path);
    }

    public function testGetProductsWithDefaultCurrency(): void
    {
        $query = 'test';
        $currency = '';

        $productMock = $this->createMock(Product::class);
        $productMock->method('getCurrency')->willReturn(Currency::EUR);
        $productMock->method('getPrice')->willReturn('100.00');
        $productMock->method('toArray')->willReturn(['id' => 1, 'price' => 85.0]);

        $rate = $this->createMock(ExchangeRates::class);
        $rate->method('getFromCurrency')->willReturn(Currency::EUR);
        $rate->method('getRate')->willReturn('0.85');

        $this->productRepository
            ->expects($this->once())
            ->method('getProducts')
            ->with($query)
            ->willReturn([$productMock]);

        $this->exchangeRatesService
            ->expects($this->once())
            ->method('getExchangeRates')
            ->with(Currency::USD)
            ->willReturn([$rate]);

        $result = $this->service->getProducts($query, $currency);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function testGetProductsWithValidCurrency(): void
    {
        $query = 'phone';
        $currency = 'EUR';

        $productMock = $this->createMock(Product::class);
        $productMock->method('getCurrency')->willReturn(Currency::USD);
        $productMock->method('getPrice')->willReturn('100.00');
        $productMock->method('toArray')->willReturn(['id' => 1, 'price' => 85.0]);

        $rate = $this->createMock(ExchangeRates::class);
        $rate->method('getFromCurrency')->willReturn(Currency::USD);
        $rate->method('getRate')->willReturn('0.85');

        $this->productRepository
            ->expects($this->once())
            ->method('getProducts')
            ->with($query)
            ->willReturn([$productMock]);

        $this->exchangeRatesService
            ->expects($this->once())
            ->method('getExchangeRates')
            ->with(Currency::EUR)
            ->willReturn([$rate]);

        $result = $this->service->getProducts($query, $currency);

        $this->assertIsArray($result);
    }

    public function testGetProductsHandlesMissingRate(): void
    {
        $query = 'test';
        $currency = 'USD';

        $productMock = $this->createMock(Product::class);
        $productMock->method('getCurrency')->willReturn(Currency::EUR);
        $productMock->method('getPrice')->willReturn('100.00');

        $this->productRepository
            ->expects($this->once())
            ->method('getProducts')
            ->with($query)
            ->willReturn([$productMock]);

        $this->exchangeRatesService
            ->expects($this->once())
            ->method('getExchangeRates')
            ->with(Currency::USD)
            ->willReturn([]);

        $result = $this->service->getProducts($query, $currency);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function testUpdateProductsSuccess(): void
    {
        $testData = [
            [
                'id' => self::UUID_1,
                'name' => 'Product 1',
                'description' => 'Description 1',
                'price' => '100.00',
                'currency' => 'USD'
            ],
            [
                'id' => self::UUID_2,
                'name' => 'Product 2',
                'description' => 'Description 2',
                'price' => '200.00',
                'currency' => 'EUR'
            ]
        ];

        $tempFile = tempnam(sys_get_temp_dir(), 'products');
        file_put_contents($tempFile, json_encode($testData));

        $this->setProductFilePath($this->service, $tempFile);

        $this->productRepository
            ->expects($this->exactly(2))
            ->method('updateProduct')
            ->with($this->callback(function (Product $product) {
                return $product->getId() !== null &&
                    $product->getName() !== null &&
                    $product->getCurrency() !== null;
            }));

        $this->productRepository
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn([self::UUID_1, self::UUID_2, self::UUID_3]);

        $this->productRepository
            ->expects($this->once())
            ->method('delete')
            ->with(self::UUID_3);

        $this->service->updateProducts();

        unlink($tempFile);
    }

    public function testUpdateProductsFileNotFound(): void
    {
        $this->setProductFilePath($this->service, '/non/existent/file.json');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Файл.*не найден/');

        $this->service->updateProducts();
    }

    public function testUpdateProductsEmptyFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'products');
        file_put_contents($tempFile, '');

        $this->setProductFilePath($this->service, $tempFile);

        $this->productRepository
            ->expects($this->never())
            ->method('updateProduct');

        $this->productRepository
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn([]);

        $this->service->updateProducts();

        unlink($tempFile);
    }

    public function testUpdateProductsInvalidCurrency(): void
    {
        $testData = [
            [
                'id' => self::UUID_1,
                'name' => 'Product 1',
                'description' => 'Description 1',
                'price' => '100.00',
                'currency' => 'INVALID'
            ]
        ];

        $tempFile = tempnam(sys_get_temp_dir(), 'products');
        file_put_contents($tempFile, json_encode($testData));

        $this->setProductFilePath($this->service, $tempFile);

        // Сервис использует tryFrom с fallback на USD, поэтому валюта будет USD
        $this->productRepository
            ->expects($this->once())
            ->method('updateProduct')
            ->with($this->callback(function (Product $product) {
                return $product->getCurrency() === Currency::USD;
            }));

        $this->productRepository
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn([self::UUID_1]);

        $this->productRepository
            ->expects($this->never())
            ->method('delete');

        $this->service->updateProducts();

        unlink($tempFile);
    }
}
