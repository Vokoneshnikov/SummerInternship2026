<?php

namespace App\Repository;

use App\Entity\ExchangeRates;
use App\Enums\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExchangeRates>
 */
class ExchangeRatesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRates::class);
    }

    public function getRatesForCurrency(Currency $currency) : array
    {
        return $this->findBy(['currency' => $currency]);
    }

    public function updateRates(array $rates): void
    {

    }
}
