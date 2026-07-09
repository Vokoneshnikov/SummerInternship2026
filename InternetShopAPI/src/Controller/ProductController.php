<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController {
    private ProductService $productService;
    public function __construct(ProductService $productService) {
        $this->productService = $productService;
    }

    #[Route('/products', name: 'products', methods: ['GET'])]
    public function index(Request $request) :JSONResponse
    {
        $query = $request->query->get('query') ?? '';
        $currency = $request->query->get('currency');

        $result = $this->productService->getProducts($query, $currency);

        return new JsonResponse($result);

    }
}
