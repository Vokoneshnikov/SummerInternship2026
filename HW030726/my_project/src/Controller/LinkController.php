<?php

namespace App\Controller;

use App\Entity\Links;
use App\Entity\User;
use App\Form\DeleteLinkType;
use App\Form\LinkType;
use App\Repository\LinksRepository;
use App\Service\LinksService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LinkController extends AbstractController {
    private LinksService $linksService;
    public function __construct(LinksService $linksService)
    {
        $this->linksService = $linksService;
    }
    #[Route('/links', name: 'home')]
    public function home(): Response
    {
        $linksData = $this->linksService->index();

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
    #[IsGranted('ROLE_USER')]
    public function createNewLink(Request $request): Response
    {
        $link = new Links();

        $form = $this->createForm(LinkType::class, $link);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var User $user */
            $user = $this->getUser();

            $this->linksService->createLink($link, $user);
        }
        return $this->render('link/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/short/{newLink}', name: 'redirect')]
    public function redirectToOriginal(string $newLink): Response
    {
        $originalUrl = $this->linksService->processRedirect($newLink);

        if ($originalUrl) {
            return $this->redirect($originalUrl);
        }
        return $this->redirectToRoute('home');
    }
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_USER')]
    public function deleteLink(Request $request, Links $link): Response
    {
        $form = $this->createForm(DeleteLinkType::class, $link);

        $form->handleRequest($request);

        /** @var User $user */
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->linksService->deleteLink($link, $user);
        }
        return $this->redirectToRoute('home');
    }
}
