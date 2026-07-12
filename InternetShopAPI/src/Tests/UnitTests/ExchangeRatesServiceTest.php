<?php

namespace App\Tests\UnitTests;

use App\Entity\ExchangeRates;
use App\Enums\Currency;
use App\Repository\ExchangeRatesRepository;
use App\Service\ExchangeRatesService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRatesServiceTest extends TestCase
{
    private ExchangeRatesRepository $repository;
    private ExchangeRatesService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ExchangeRatesRepository::class);
    }

    public function testUpdateRatesSuccess(): void
    {
        $mockResponses = [];
        foreach (Currency::cases() as $currency) {
            $mockResponses[] = new MockResponse(json_encode([
                'base' => $currency->value,
                'rates' => [
                    'EUR' => 0.85,
                    'GBP' => 0.73,
                    'RUB' => 90.5,
                ]
            ]));
        }

        $mockHttpClient = new MockHttpClient($mockResponses);
        $service = new ExchangeRatesService($this->repository, $mockHttpClient);

        // В каждом ответе 3 валюты → для каждой базовой валюты будет 3 вызова
        $expectedCalls = count(Currency::cases()) * 3;

        $this->repository
            ->expects($this->exactly($expectedCalls))
            ->method('updateRate')
            ->with($this->callback(function (ExchangeRates $rate) {
                return $rate->getFromCurrency() !== null &&
                    $rate->getRate() > 0;
            }));

        $service->updateRates();
    }

    public function testUpdateRatesSkipsInvalidCurrency(): void
    {
        $mockResponses = [];
        foreach (Currency::cases() as $currency) {
            if ($currency === Currency::USD) {
                $mockResponses[] = new MockResponse(json_encode([
                    'base' => 'USD',
                    'rates' => [
                        'INVALID' => 1.0,
                        'EUR' => 0.85,
                    ]
                ]));
            } else {
                $mockResponses[] = new MockResponse(json_encode([
                    'base' => $currency->value,
                    'rates' => [
                        'EUR' => 0.85,
                        'USD' => 1.18,
                    ]
                ]));
            }
        }

        $mockHttpClient = new MockHttpClient($mockResponses);
        $service = new ExchangeRatesService($this->repository, $mockHttpClient);

        // Для USD только 1 валидная валюта (EUR), для остальных – по 2
        $expectedCalls = 1 + ((count(Currency::cases()) - 1) * 2);

        $this->repository
            ->expects($this->exactly($expectedCalls))
            ->method('updateRate');

        $service->updateRates();
    }

    public function testUpdateRatesHandlesApiError(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('API Error'));

        $service = new ExchangeRatesService($this->repository, $mockHttpClient);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API Error');

        $service->updateRates();
    }

    public function testUpdateRatesHandlesMissingBaseCurrency(): void
    {
        $mockResponses = [];
        foreach (Currency::cases() as $currency) {
            if ($currency === Currency::USD) {
                $mockResponses[] = new MockResponse(json_encode([
                    'base' => 'INVALID_CURRENCY',
                    'rates' => [
                        'EUR' => 0.85,
                        'GBP' => 0.73,
                    ]
                ]));
            } else {
                $mockResponses[] = new MockResponse(json_encode([
                    'base' => $currency->value,
                    'rates' => [
                        'EUR' => 0.85,
                        'USD' => 1.18,
                    ]
                ]));
            }
        }

        $mockHttpClient = new MockHttpClient($mockResponses);
        $service = new ExchangeRatesService($this->repository, $mockHttpClient);

        $this->expectException(\TypeError::class);

        $service->updateRates();
    }

    public function testUpdateRatesHandlesEmptyRates(): void
    {
        $mockResponses = [];
        foreach (Currency::cases() as $currency) {
            if ($currency === Currency::USD) {
                $mockResponses[] = new MockResponse(json_encode([
                    'base' => 'USD',
                    'rates' => []
                ]));
            } else {
                $mockResponses[] = new MockResponse(json_encode([
                    'base' => $currency->value,
                    'rates' => [
                        'EUR' => 0.85,
                        'USD' => 1.18,
                    ]
                ]));
            }
        }

        $mockHttpClient = new MockHttpClient($mockResponses);
        $service = new ExchangeRatesService($this->repository, $mockHttpClient);

        // Для USD не будет вызовов, для остальных – по 2
        $expectedCalls = (count(Currency::cases()) - 1) * 2;

        $this->repository
            ->expects($this->exactly($expectedCalls))
            ->method('updateRate');

        $service->updateRates();
    }

    public function testUpdateRatesProcessesAllCurrencies(): void
    {
        $mockResponses = [];
        foreach (Currency::cases() as $currency) {
            $mockResponses[] = new MockResponse(json_encode([
                'base' => $currency->value,
                'rates' => [
                    'EUR' => 0.85,
                    'USD' => 1.18,
                    'GBP' => 0.73,
                ]
            ]));
        }

        $mockHttpClient = new MockHttpClient($mockResponses);
        $service = new ExchangeRatesService($this->repository, $mockHttpClient);

        $expectedCalls = count(Currency::cases()) * 3;
        $this->repository
            ->expects($this->exactly($expectedCalls))
            ->method('updateRate');

        $service->updateRates();
    }

    public function testGetExchangeRates(): void
    {
        $expectedRates = [
            $this->createMock(ExchangeRates::class),
            $this->createMock(ExchangeRates::class),
        ];

        $this->repository
            ->expects($this->once())
            ->method('getRatesForCurrency')
            ->with(Currency::USD)
            ->willReturn($expectedRates);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $service = new ExchangeRatesService($this->repository, $mockHttpClient);

        $result = $service->getExchangeRates(Currency::USD);

        $this->assertSame($expectedRates, $result);
    }

    public function testGetExchangeRatesReturnsEmptyArray(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('getRatesForCurrency')
            ->with(Currency::EUR)
            ->willReturn([]);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $service = new ExchangeRatesService($this->repository, $mockHttpClient);

        $result = $service->getExchangeRates(Currency::EUR);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testUpdateRatesWithMultipleRatesForOneCurrency(): void
    {
        $mockResponses = [];
        foreach (Currency::cases() as $currency) {
            if ($currency === Currency::USD) {
                $mockResponses[] = new MockResponse(json_encode([
                    'base' => 'USD',
                    'rates' => [
                        'EUR' => 0.85,
                        'GBP' => 0.73,
                        'RUB' => 90.5,
                        'CAD' => 1.25,
                        'CHF' => 0.92,
                        'CNY' => 6.45,
                    ]
                ]));
            } else {
                $mockResponses[] = new MockResponse(json_encode([
                    'base' => $currency->value,
                    'rates' => [
                        'EUR' => 0.85,
                        'USD' => 1.18,
                    ]
                ]));
            }
        }

        $mockHttpClient = new MockHttpClient($mockResponses);
        $service = new ExchangeRatesService($this->repository, $mockHttpClient);

        $expectedCalls = 6 + ((count(Currency::cases()) - 1) * 2);

        $this->repository
            ->expects($this->exactly($expectedCalls))
            ->method('updateRate');

        $service->updateRates();
    }

    public function testUpdateRatesWithDuplicateCurrencies(): void
    {
        $mockResponses = [];
        foreach (Currency::cases() as $currency) {
            $mockResponses[] = new MockResponse(json_encode([
                'base' => $currency->value,
                'rates' => [
                    'EUR' => 0.85,
                    'USD' => 1.18,
                ]
            ]));
        }

        $mockHttpClient = new MockHttpClient($mockResponses);
        $service = new ExchangeRatesService($this->repository, $mockHttpClient);

        $expectedCalls = count(Currency::cases()) * 2;
        $this->repository
            ->expects($this->exactly($expectedCalls))
            ->method('updateRate');

        $service->updateRates();
    }
}
