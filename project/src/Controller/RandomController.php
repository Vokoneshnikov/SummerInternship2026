<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RandomController {
    #[Route('/random/only-one')]
    public function number() : Response
    {
        $number = random_int(0, 100);

        return new Response('<html><body>Lucky number: '.$number.'</body></html>');
    }

    #[Route('/random/many/{amount}')]
    public function numbers(int $amount) : Response {
        $result = [];
        for ($i = 0; $i < $amount; $i++) {
            $result[] = random_int(0, 100);
        }
        return new Response('<html><body>Lucky numbers: '.implode(',', $result).'</body></html>');
    }

    #[Route('/test')]
    public function test(Request $request) : Response {

        $allParams = $request->query->all();

        return new Response('<html><body><h1>' . print_r($allParams, true) . '</h1></body></html>');
    }
}
