<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LinkController extends AbstractController {

    #[Route('/create', name: 'create')]
    public function createShortLink() : Response
    {
        #Возвращает созданную сокращенную ссылку формата https.../short/{id}

        $link = "link"; #здесь работа репозитория
        return new Response("<html><body>Here is your short link:" . $link. "</body></html>");
    }
    #[Route('/short/{id}', name: 'redirect')]
    public function redirectToOriginal(int $id)
    {
        #Делает редирект на страницу оригинальной ссылки
    }
    #[Route('/delete/{id}', name: 'delete')]
    public function deleteLink(int $id)
    {
        #Удаляем короткую ссылку из БД
    }
}
