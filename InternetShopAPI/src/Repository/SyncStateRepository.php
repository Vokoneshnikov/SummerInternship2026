<?php

namespace App\Repository;

use App\Entity\SyncState;
use App\Enums\SyncType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SyncState>
 */
class SyncStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SyncState::class);
    }

    public function findByType(SyncType $syncType): ?SyncState
    {
        return $this->createQueryBuilder('s')
            ->where('s.type = :type')
            ->setParameter('type', $syncType)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function updateTime(SyncType $syncType): void
    {
        $syncState = $this->findByType($syncType);

        $syncState->setLastSyncAt(new \DateTimeImmutable());
        if ($syncType === SyncType::RATES) {
            $syncState->setNextSyncAt((new \DateTimeImmutable())->modify('+1 hour'));
        } else {
            $syncState->setNextSyncAt((new \DateTimeImmutable())->modify('+12 hour'));
        }
        $this->getEntityManager()->persist($syncState);
        $this->getEntityManager()->flush();
    }
    public function getLastSyncTime(SyncType $type): ?\DateTimeInterface
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.last_sync_at')
            ->where('s.type = :type')
            ->setParameter('type', $type->value)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['last_sync_at'] : null;
    }
    public function getNextSyncTime(SyncType $type): ?\DateTimeInterface
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.next_sync_at')
            ->where('s.type = :type')
            ->setParameter('type', $type->value)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['next_sync_at'] : null;
    }
}
