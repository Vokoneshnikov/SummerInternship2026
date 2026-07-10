<?php

namespace App\Service;

use App\Enums\SyncType;
use App\Repository\SyncStateRepository;

class SyncStateService {

    public function __construct(private SyncStateRepository $syncStateRepository) {}


    public function isUpdated(SyncType $syncType): bool {
        $syncState = $this->syncStateRepository->findByType($syncType);

        return $syncState->getNextSyncAt() > new \DateTime('now');
    }



    public function updateTime(SyncType $syncType) : void
    {
        $this->syncStateRepository->updateTime($syncType);
    }
}
