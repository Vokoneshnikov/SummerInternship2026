<?php

namespace App\Enums;

enum SyncType: string
{
    case PRICES = 'prices';
    case RATES = 'rates';

    public function getInterval(): int
    {
        return match($this) {
            self::PRICES => 12, // часов
            self::RATES => 1,   // час
        };
    }
    public function getIntervalInSeconds(): int
    {
        return $this->getInterval() * 3600;
    }

    public function getLockName(): string
    {
        return match($this) {
            self::PRICES => 'update_prices',
            self::RATES => 'update_rates',
        };
    }
}
