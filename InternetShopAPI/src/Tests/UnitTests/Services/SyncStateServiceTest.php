<?php

namespace App\Tests\UnitTests\Services;

use App\Entity\SyncState;
use App\Enums\SyncType;
use App\Repository\SyncStateRepository;
use App\Service\SyncStateService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SyncStateServiceTest extends KernelTestCase
{
    private SyncStateRepository $repository;
    private SyncStateService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(SyncStateRepository::class);
        $this->service = new SyncStateService($this->repository);
    }

    public function testIsUpdatedReturnsTrueWhenNextSyncInFuture(): void
    {
        $syncState = $this->createMock(SyncState::class);
        $syncState
            ->method('getNextSyncAt')
            ->willReturn(new \DateTimeImmutable('+1 hour'));

        $this->repository
            ->expects($this->once())
            ->method('findByType')
            ->with(SyncType::RATES)
            ->willReturn($syncState);

        $result = $this->service->isUpdated(SyncType::RATES);

        $this->assertTrue($result);
    }

    public function testIsUpdatedReturnsFalseWhenNextSyncInPast(): void
    {
        $syncState = $this->createMock(SyncState::class);
        $syncState
            ->method('getNextSyncAt')
            ->willReturn(new \DateTimeImmutable('-1 hour'));

        $this->repository
            ->expects($this->once())
            ->method('findByType')
            ->with(SyncType::PRICES)
            ->willReturn($syncState);

        $result = $this->service->isUpdated(SyncType::PRICES);

        $this->assertFalse($result);
    }

    public function testIsUpdatedReturnsFalseWhenNextSyncNow(): void
    {
        $syncState = $this->createMock(SyncState::class);
        $syncState
            ->method('getNextSyncAt')
            ->willReturn(new \DateTimeImmutable('now'));

        $this->repository
            ->expects($this->once())
            ->method('findByType')
            ->with(SyncType::RATES)
            ->willReturn($syncState);

        $result = $this->service->isUpdated(SyncType::RATES);

        $this->assertFalse($result);
    }

    public function testIsUpdatedWithNullSyncState(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findByType')
            ->with(SyncType::PRICES)
            ->willReturn(null);

        // В PHP 8 вызов метода на null выбрасывает Error
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function getNextSyncAt() on null');

        $this->service->isUpdated(SyncType::PRICES);
    }

    public function testUpdateTimeForRates(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('updateTime')
            ->with(SyncType::RATES);

        $this->service->updateTime(SyncType::RATES);
    }

    public function testUpdateTimeForPrices(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('updateTime')
            ->with(SyncType::PRICES);

        $this->service->updateTime(SyncType::PRICES);
    }
}
