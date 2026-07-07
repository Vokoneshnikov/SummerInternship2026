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

    public function updateRate(ExchangeRates $rate): void
    {
        $result = $this->findOneBy([
            'from_currency' => $rate->getFromCurrency(),
            'toCurrency' => $rate->getToCurrency()
        ]);

        if ($result === null) {
            $this->createRate($rate);
            return;
        }

        $result->setFromCurrency($rate->getFromCurrency());
        $result->setToCurrency($rate->getToCurrency());
        $result->setRate($rate->getRate());

        $this->getEntityManager()->flush();
    }
    private function createRate(ExchangeRates $rate) : void
    {
        $this->getEntityManager()->persist($rate);
        $this->getEntityManager()->flush();
    }
}
