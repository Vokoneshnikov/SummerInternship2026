<?php

namespace App\Entity;

use App\Enums\Currency;
use App\Repository\ExchangeRatesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;

#[ORM\Entity(repositoryClass: ExchangeRatesRepository::class)]
//#[Broadcast]
class ExchangeRates
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: Currency::class)]
    private ?Currency $from_currency = null;

    #[ORM\Column(enumType: Currency::class)]
    private ?Currency $toCurrency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 10)]
    private ?string $rate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromCurrency(): ?Currency
    {
        return $this->from_currency;
    }

    public function setFromCurrency(Currency $from_currency): static
    {
        $this->from_currency = $from_currency;

        return $this;
    }

    public function getToCurrency(): ?Currency
    {
        return $this->toCurrency;
    }

    public function setToCurrency(Currency $toCurrency): static
    {
        $this->toCurrency = $toCurrency;

        return $this;
    }

    public function getRate(): ?string
    {
        return $this->rate;
    }

    public function setRate(string $rate): static
    {
        $this->rate = $rate;

        return $this;
    }
}
