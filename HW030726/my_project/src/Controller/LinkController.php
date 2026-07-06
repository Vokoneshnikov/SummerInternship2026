<?php

namespace App\Controller;

use App\Repository\LinksRepository;
use App\Service\LinksService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LinkController extends AbstractController {
    private LinksService $linksService;
    public function __construct(LinksService $linksService)
    {
        $this->linksService = $linksService;
    }
    #[Route('/links', name: 'home')]
    public function home(): Response
    {
        $links = $this->linksService->index();

        // Преобразуем объекты в массивы для шаблона
        $linksData = array_map(function($link) {
            return [
                'id' => $link->getId(),
                'oldLink' => $link->getOldLink(),
                'newLink' => $link->getNewLink(),
                'createdAt' => $link->getCreatedAt(),
                'lastUsedAt' => $link->getLastUsedAt(),
                'usageCount' => $link->getUsageCount(),
            ];
        }, $links);

        return $this->render('link/index.html.twig', [
            'links' => $linksData,
        ]);
    }
    #[Route('/', name: 'create_page')]
    public function createPage(): Response
    {
        return $this->render('link/create.html.twig');
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function createNewLink(Request $request): JsonResponse
    {
        $link = $request->request->get('link');

        if (!$link) {
            return $this->json(['error' => 'URL is required'], 400);
        }

        try {
            $shortUrl = $this->linksService->createLink($link);

            return $this->json([
                'shortUrl' => $shortUrl
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
    #[Route('/short/{newLink}', name: 'redirect')]
    public function redirectToOriginal(string $newLink): Response
    {
        $originalUrl = $this->linksService->getOriginalLink($newLink);
        $this->linksService->updateLink($newLink);

        return $this->redirect($originalUrl);
    }
    #[Route('/delete/{id}', name: 'delete')]
    public function deleteLink(int $id)
    {
        $this->linksService->deleteLink($id);

        return new Response("<html><body>Deleted!</body></html>");
    }
}
