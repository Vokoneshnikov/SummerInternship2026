<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\ExchangeRatesService;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExchangeRatesController extends AbstractController {
    private ExchangeRatesService $service;
    public function __construct(ExchangeRatesService $service) {
        $this->service = $service;
    }

    #[Route('/rates', name: 'rates', methods: ['GET'])]
    public function index() : Response
    {
        $this->service->updateRates();

        return new Response('', Response::HTTP_OK);
    }

}
