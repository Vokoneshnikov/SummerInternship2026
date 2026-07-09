<?php

namespace App\Repository;

use App\Entity\CachedWeather;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CachedWeather>
 */
class CachedWeatherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CachedWeather::class);
    }

    /**
     * Получить валидные данные по городу (не просроченные)
     */
    public function findValidByCity(string $cityName): ?CachedWeather
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('c')
            ->where('c.cityName = :city')
            ->andWhere('c.expiresAt > :now')
            ->setParameter('city', $cityName)
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();


    }

    /**
     * Удалить просроченные записи (для автоматической очистки)
     */
    public function deleteExpired(): int
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('c')
            ->delete()
            ->where('c.expiresAt <= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->execute();
    }

    /**
     * Удалить запись по городу (для опции --refresh)
     */
    public function deleteByCity(string $cityName): int
    {
        return $this->createQueryBuilder('c')
            ->delete()
            ->where('c.cityName = :city')
            ->setParameter('city', strtolower(trim($cityName)))
            ->getQuery()
            ->execute();
    }
    public function save(CachedWeather $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

    }
    /**
     * Получить статистику кеша (для опции --stats)
     */
    public function getStats(): array
    {
        $now = new \DateTimeImmutable();

        $total = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $valid = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.expiresAt > :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'valid' => $valid,
            'expired' => $total - $valid,
        ];
    }
}
