<?php

namespace App\Entity;

use App\Enums\Currency;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
//#[Broadcast]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?UuidInterface $id = null;

    #[ORM\Column(length: 128)]
    private ?string $name = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 9, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(enumType: Currency::class)]
    private ?Currency $currency = null;

    public function setIdFromString(string $id): self
    {
        $this->id = Uuid::fromString($id);
        return $this;
    }
    public function getId(): ?UuidInterface
    {
        return $this->id;
    }
    public function setId(?UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'currency' => $this->currency->value,
        ];
    }
}
