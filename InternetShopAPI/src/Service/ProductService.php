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
        $currEnum = Currency::tryFrom(strtoupper($currency));

        if ($currEnum === null) {
            $currEnum = Currency::USD;
        }

        $products = $this->productRepository->getProducts($query);
        $rates = $this->exchangeRatesService->getExchangeRates($currEnum);

        foreach ($products as $product) {
            $curr = $product->getCurrency();
            $price = $product->getPrice();
            $rate = $rates[$curr];

            $newPrice = $price * $rate;

            $product->setPrice($newPrice);
        }
        return $products;
    }
    public function updateProducts(string $json) : void
    {
        $array = json_decode($json, true);

        if ($array === null) {
            return;
        }
        foreach ($array as $item) {
            $product = new Product();
            $product->setId($item['id']);
            $product->setName($item['name']);
            $product->setDescription($item['description']);
            $product->setPrice($item['price']);
            $product->setCurrency($item['currency']);

            $this->productRepository->updateProduct($product);
        }
    }
}
