<?php

namespace App\Service;

use App\Entity\Product;
use App\Enums\Currency;
use App\Repository\ExchangeRatesRepository;
use App\Repository\ProductRepository;

class ProductService {


    public function __construct(private readonly ProductRepository $productRepository, private readonly ExchangeRatesService $exchangeRatesService) {}

    public function getProducts(string $query, string $currency) : array
    {

        $toCurrencyEnum = Currency::tryFrom(strtoupper($currency)) ?? Currency::USD;

        $products = $this->productRepository->getProducts($query);
        $rates = $this->exchangeRatesService->getExchangeRates($toCurrencyEnum);

        $realRates = [];

        foreach ($rates as $rate) {
            $realRates[$rate->getFromCurrency()->value] = $rate->getRate();
        }

        $result = [];

        foreach ($products as $product) {
            $curr = $product->getCurrency()->value;
            $price = $product->getPrice();
            $rate = $realRates[$curr];

            $newPrice = $price * $rate;

            $product->setPrice($newPrice);
            $result[] = $product->toArray();
        }
        return $result;
    }
    public function updateProducts() : void
    {
        $jsonString = file_get_contents('../products.json');

        $array = json_decode($jsonString, true);

        $usedIds = [];

        foreach ($array as $item) {
            $usedIds[] = $item['id'];

            $product = new Product();
            $product->setId($item['id']);
            $product->setName($item['name']);
            $product->setDescription($item['description']);
            $product->setPrice($item['price']);
            $product->setCurrency(Currency::tryFrom($item['currency']));

            $this->productRepository->updateProduct($product);
        }
        $allIds = $this->productRepository->getAllIds();

        $deleted = array_diff($allIds, $usedIds);

        foreach ($deleted as $id) {
            $this->productRepository->delete($id);
        }
    }
}
