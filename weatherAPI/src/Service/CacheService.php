<?php

namespace App\Service;

use App\Entity\CachedWeather;
use App\Repository\CachedWeatherRepository;
use Doctrine\ORM\EntityManagerInterface;

class CacheService
{
    private CachedWeatherRepository $repository;
    private int $ttl;

    public function __construct(CachedWeatherRepository $repository, int $ttl = 300)
    {
        $this->repository = $repository;
        $this->ttl = $ttl;
    }

    /**
     * Получить данные из кеша
     * Возвращает null если данных нет или они просрочены
     */
    public function get(string $cityName): ?array
    {
        $cached = $this->repository->findValidByCity($cityName);

        if (!$cached) {

            return null;
        }
        return $cached->getWeatherData();
    }

    /**
     * Сохранить данные в кеш
     */
    public function set(string $cityName, array $data): void
    {
        $cityName = strtolower(trim($cityName));

        // Ищем существующую запись
        $cached = $this->repository->findOneBy(['cityName' => $cityName]);

        if (!$cached) {
            $cached = new CachedWeather();
            $cached->setCityName($cityName);
            $cached->setCreatedAt(new \DateTimeImmutable());
        }

        $cached->setWeatherData($data);
        $cached->setExpiresAt(
            (new \DateTimeImmutable())->modify("+{$this->ttl} seconds")
        );

        $this->repository->save($cached);
    }

    /**
     * Удалить кеш для города (для опции --refresh)
     */
    public function delete(string $cityName): void
    {
        $this->repository->deleteByCity($cityName);
    }

    /**
     * Очистить просроченные записи
     */
    public function cleanup(): void
    {
        $this->repository->deleteExpired();
    }

    /**
     * Получить статистику (для опции --stats)
     */
    public function getStats(): array
    {
        return $this->repository->getStats();
    }
}
