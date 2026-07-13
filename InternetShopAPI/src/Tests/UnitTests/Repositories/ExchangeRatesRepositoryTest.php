<?php

namespace App\Tests\UnitTests\Repositories;

use App\Entity\ExchangeRates;
use App\Enums\Currency;
use App\Repository\ExchangeRatesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExchangeRatesRepositoryTest extends KernelTestCase
{
    private ExchangeRatesRepository $repository;
    private EntityManagerInterface $entityManager;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry
            ->method('getManagerForClass')
            ->with(ExchangeRates::class)
            ->willReturn($this->entityManager);

        $this->repository = $this->getMockBuilder(ExchangeRatesRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['findBy', 'findOneBy', 'getEntityManager'])
            ->getMock();
    }

    public function testGetRatesForCurrencyReturnsRates(): void
    {
        $currency = Currency::USD;
        $expectedRates = [
            $this->createMock(ExchangeRates::class),
            $this->createMock(ExchangeRates::class),
        ];

        $this->repository
            ->expects($this->once())
            ->method('findBy')
            ->with(['toCurrency' => $currency])
            ->willReturn($expectedRates);

        $result = $this->repository->getRatesForCurrency($currency);

        $this->assertSame($expectedRates, $result);
        $this->assertCount(2, $result);
    }

    public function testGetRatesForCurrencyReturnsEmptyArray(): void
    {
        $currency = Currency::EUR;

        $this->repository
            ->expects($this->once())
            ->method('findBy')
            ->with(['toCurrency' => $currency])
            ->willReturn([]);

        $result = $this->repository->getRatesForCurrency($currency);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testUpdateRateUpdatesExistingRate(): void
    {
        $rate = $this->createMock(ExchangeRates::class);
        $rate->method('getFromCurrency')->willReturn(Currency::USD);
        $rate->method('getToCurrency')->willReturn(Currency::EUR);
        $rate->method('getRate')->willReturn('0.85');

        $existingRate = $this->createMock(ExchangeRates::class);
        $existingRate->expects($this->once())->method('setFromCurrency')->with(Currency::USD);
        $existingRate->expects($this->once())->method('setToCurrency')->with(Currency::EUR);
        $existingRate->expects($this->once())->method('setRate')->with('0.85');

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'from_currency' => Currency::USD,
                'toCurrency' => Currency::EUR
            ])
            ->willReturn($existingRate);

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

        $this->repository->updateRate($rate);
    }

    public function testUpdateRateCreatesNewRateWhenNotExists(): void
    {
        $rate = $this->createMock(ExchangeRates::class);
        $rate->method('getFromCurrency')->willReturn(Currency::USD);
        $rate->method('getToCurrency')->willReturn(Currency::EUR);
        $rate->method('getRate')->willReturn('0.85');

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'from_currency' => Currency::USD,
                'toCurrency' => Currency::EUR
            ])
            ->willReturn(null);

        $this->repository
            ->expects($this->exactly(2))
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($rate);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->updateRate($rate);
    }

    /**
     * Тест с разными валютами, используя willReturnCallback вместо at().
     */
    public function testUpdateRateWithDifferentCurrencies(): void
    {
        $testCases = [
            ['from' => Currency::USD, 'to' => Currency::EUR, 'rate' => '0.85'],
            ['from' => Currency::EUR, 'to' => Currency::GBP, 'rate' => '0.73'],
            ['from' => Currency::USD, 'to' => Currency::RUB, 'rate' => '90.5'],
        ];

        // Счётчик вызовов для последовательной отдачи разных моков
        $callIndex = 0;

        $this->repository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria) use ($testCases, &$callIndex) {
                // Проверяем, что критерии соответствуют текущему тестовому случаю
                $expected = $testCases[$callIndex];
                $this->assertEquals(['from_currency' => $expected['from'], 'toCurrency' => $expected['to']], $criteria);

                // Создаём новый мок существующего курса для каждого вызова
                $existingRate = $this->createMock(ExchangeRates::class);
                $existingRate->expects($this->once())->method('setFromCurrency')->with($expected['from']);
                $existingRate->expects($this->once())->method('setToCurrency')->with($expected['to']);
                $existingRate->expects($this->once())->method('setRate')->with($expected['rate']);

                $callIndex++;
                return $existingRate;
            });

        $this->repository
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->exactly(count($testCases)))
            ->method('flush');

        // Выполняем обновления для каждого случая
        foreach ($testCases as $testCase) {
            $rate = $this->createMock(ExchangeRates::class);
            $rate->method('getFromCurrency')->willReturn($testCase['from']);
            $rate->method('getToCurrency')->willReturn($testCase['to']);
            $rate->method('getRate')->willReturn($testCase['rate']);

            $this->repository->updateRate($rate);
        }
    }

    public function testUpdateRateWithNullValues(): void
    {
        $rate = $this->createMock(ExchangeRates::class);
        $rate->method('getFromCurrency')->willReturn(null);
        $rate->method('getToCurrency')->willReturn(null);
        $rate->method('getRate')->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'from_currency' => null,
                'toCurrency' => null
            ])
            ->willReturn(null);

        $this->repository
            ->expects($this->exactly(2))
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($rate);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->updateRate($rate);
    }

    public function testUpdateRateCreatesNewRateWithFullData(): void
    {
        $rate = $this->createMock(ExchangeRates::class);
        $rate->method('getFromCurrency')->willReturn(Currency::USD);
        $rate->method('getToCurrency')->willReturn(Currency::EUR);
        $rate->method('getRate')->willReturn('0.8512');

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->repository
            ->expects($this->exactly(2))
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($rate);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->updateRate($rate);
    }

    /**
     * Тест с различными курсами, используя willReturnCallback для создания свежих моков.
     */
    public function testUpdateRateWithVariousRates(): void
    {
        $rates = [
            ['from' => Currency::USD, 'to' => Currency::EUR, 'rate' => '0.85'],
            ['from' => Currency::USD, 'to' => Currency::GBP, 'rate' => '0.75'],
            ['from' => Currency::EUR, 'to' => Currency::USD, 'rate' => '1.18'],
            ['from' => Currency::EUR, 'to' => Currency::GBP, 'rate' => '0.88'],
        ];

        $callIndex = 0;

        $this->repository
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria) use ($rates, &$callIndex) {
                $expected = $rates[$callIndex];
                // Необязательная проверка критериев
                $this->assertEquals(['from_currency' => $expected['from'], 'toCurrency' => $expected['to']], $criteria);

                $existingRate = $this->createMock(ExchangeRates::class);
                $existingRate->expects($this->once())->method('setFromCurrency')->with($expected['from']);
                $existingRate->expects($this->once())->method('setToCurrency')->with($expected['to']);
                $existingRate->expects($this->once())->method('setRate')->with($expected['rate']);

                $callIndex++;
                return $existingRate;
            });

        $this->repository
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->exactly(count($rates)))
            ->method('flush');

        foreach ($rates as $data) {
            $rate = $this->createMock(ExchangeRates::class);
            $rate->method('getFromCurrency')->willReturn($data['from']);
            $rate->method('getToCurrency')->willReturn($data['to']);
            $rate->method('getRate')->willReturn($data['rate']);

            $this->repository->updateRate($rate);
        }
    }
}
