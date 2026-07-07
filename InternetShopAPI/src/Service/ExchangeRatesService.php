<?php

namespace App\Service;

use App\Entity\ExchangeRates;
use App\Enums\Currency;
use App\Repository\ExchangeRatesRepository;
use Symfony\Component\HttpClient\HttpClient;

class ExchangeRatesService {

    public function __construct(private ExchangeRatesRepository $repository) {}

    public function updateRates(): void
    {
        foreach (Currency::cases() as $currency) {

            $client = HttpClient::create();
            $response = $client->request('GET', "https://api.exchangerate.fun/latest?base=" . $currency->name);

            $array = $response->toArray();

            $base = Currency::tryFrom($array['base']);
            $rates = $array['rates'];

            foreach ($rates as $toCurrency => $rate) {

                $newRate = new ExchangeRates();

                $newRate->setFromCurrency($base);
                $newRate->setToCurrency(Currency::tryFrom($toCurrency));
                $newRate->setRate($rate);

                $this->repository->updateRate($newRate);
            }
        }

    }

    public function getExchangeRates(Currency $curr): array
    {
        return $this->repository->getRatesForCurrency($curr);
    }
}
