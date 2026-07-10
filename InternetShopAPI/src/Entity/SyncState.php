<?php

namespace App\Entity;

use App\Enums\SyncType;
use App\Repository\SyncStateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SyncStateRepository::class)]
class SyncState
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: SyncType::class)]
    private ?SyncType $type = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $last_sync_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $next_sync_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?SyncType
    {
        return $this->type;
    }

    public function setType(SyncType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLastSyncAt(): ?\DateTimeImmutable
    {
        return $this->last_sync_at;
    }

    public function setLastSyncAt(?\DateTimeImmutable $last_sync_at): static
    {
        $this->last_sync_at = $last_sync_at;

        return $this;
    }

    public function getNextSyncAt(): ?\DateTimeImmutable
    {
        return $this->next_sync_at;
    }

    public function setNextSyncAt(?\DateTimeImmutable $next_sync_at): static
    {
        $this->next_sync_at = $next_sync_at;

        return $this;
    }
}
