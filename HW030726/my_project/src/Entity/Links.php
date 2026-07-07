<?php

namespace App\Entity;

use App\Repository\LinksRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LinksRepository::class)]
class Links
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "URL не может быть пустым")]
    #[Assert\Url(message: "Введите корректный URL")]
    private ?string $oldLink = null;

    #[ORM\Column(length: 255)]
    private ?string $newLink = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastUsedAt = null;

    #[ORM\Column]
    private ?int $usageCount = null;

    #[ORM\Column]
    private ?bool $isDisposable = false;

    #[ORM\ManyToOne(inversedBy: 'links')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[Assert\Date]
    #[Assert\GreaterThan(
        value: "today"
    )]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOldLink(): ?string
    {
        return $this->oldLink;
    }

    public function setOldLink(string $oldLink): static
    {
        $this->oldLink = $oldLink;

        return $this;
    }

    public function getNewLink(): ?string
    {
        return $this->newLink;
    }

    public function setNewLink(string $newLink): static
    {
        $this->newLink = $newLink;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(\DateTimeImmutable $lastUsedAt): static
    {
        $this->lastUsedAt = $lastUsedAt;

        return $this;
    }

    public function getUsageCount(): ?int
    {
        return $this->usageCount;
    }

    public function setUsageCount(int $usageCount): static
    {
        $this->usageCount = $usageCount;

        return $this;
    }

    public function isDisposable(): ?bool
    {
        return $this->isDisposable;
    }

    public function setIsDisposable(bool $isDisposable): static
    {
        $this->isDisposable = $isDisposable;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        // Если передана строка, преобразуем в DateTimeImmutable
        if (is_string($expiresAt)) {
            try {
                $expiresAt = new \DateTimeImmutable($expiresAt);
            } catch (\Exception $e) {
                $expiresAt = null;
            }
        }

        $this->expiresAt = $expiresAt;
        return $this;
    }
}
