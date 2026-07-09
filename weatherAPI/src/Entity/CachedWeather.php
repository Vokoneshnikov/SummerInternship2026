<?php

namespace App\Entity;

use App\Repository\CachedWeatherRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CachedWeatherRepository::class)]
#[ORM\Table(name: 'cached_weather')]
#[ORM\Index(columns: ['city_name'], name: 'idx_city_name')]
#[ORM\Index(columns: ['expires_at'], name: 'idx_expires_at')]
class CachedWeather
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'city_name', type: Types::STRING, length: 100, unique: true)]
    private string $cityName;

    #[ORM\Column(name: 'weather_data', type: Types::JSON)]
    private array $weatherData = [];

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'expires_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $expiresAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = (new \DateTimeImmutable())->modify('+5 minutes');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function setCityName(string $cityName): self
    {
        $this->cityName = strtolower(trim($cityName));
        return $this;
    }

    public function getWeatherData(): array
    {
        return $this->weatherData;
    }

    public function setWeatherData(array $weatherData): self
    {
        $this->weatherData = $weatherData;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->expiresAt;
    }

    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    public function refresh(int $ttl = 300): self
    {
        $this->expiresAt = (new \DateTimeImmutable())->modify("+{$ttl} seconds");
        return $this;
    }

    public function getTimeToLive(): int
    {
        $now = new \DateTimeImmutable();
        if ($now > $this->expiresAt) {
            return 0;
        }
        return $this->expiresAt->getTimestamp() - $now->getTimestamp();
    }
}
