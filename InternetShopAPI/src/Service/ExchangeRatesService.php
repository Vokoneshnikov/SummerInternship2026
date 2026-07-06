<?php

namespace App\Service;

use App\Entity\ExchangeRates;
use App\Enums\Currency;
use App\Repository\ExchangeRatesRepository;

class ExchangeRatesService {

    public function __construct(private ExchangeRatesRepository $repository) {}

    public function updateRates(): void
    {
        $rates = [];
        foreach (Currency::cases() as $currency) {

            //ВОТ ТУТ НАДО БИЗНЕС ЛОГИКУ ЗАПРОСА К АПИ ОПИСАТЬ
            $apiRequest = "https://api.exchangerate.fun/latest?base=" . $currency->name;

            $apiResult = ...;

            $array = json_decode($apiResult, true);

            $base = Currency::tryFrom($array['base']);
            $rates = $array['rates'];

            foreach ($rates as $toCurrency => $rate) {

                $newRate = new ExchangeRates();

                $newRate->setFromCurrency($base);
                $newRate->setToCurrency(Currency::tryFrom($toCurrency));
                $newRate->setRate($rate);

                $rates[] = $newRate;
            }
        }

        $this->repository->updateRates($rates);
    }

    public function getExchangeRates(Currency $curr): array
    {
        return $this->repository->getRatesForCurrency($curr);
    }
}
