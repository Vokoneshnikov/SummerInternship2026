<?php

namespace App\Command;

use App\Entity\SyncState;
use App\Enums\SyncType;
use App\Service\ExchangeRatesService;
use App\Service\SyncStateService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\LockFactory;

#[AsCommand('app:update-rates')]
class UpdateRatesCommand extends Command {
    private const LOCK_TTL = 120;
    public function __construct(
        private ExchangeRatesService $exchangeRatesService,
        private SyncStateService $syncStateService,
        private LockFactory $lockFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);

        $lock = $this->lockFactory->createLock('update_rates', self::LOCK_TTL);

        if (!$lock->acquire()) {
            $io->warning('Поток занят');
            return Command::SUCCESS;
        }

        try {
            if (!$this->syncStateService->isUpdated(SyncType::RATES)) {
                $this->exchangeRatesService->updateRates();
                $this->syncStateService->updateTime(SyncType::RATES);
            }
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        } finally {
            $lock->release();
        }
    }
}
