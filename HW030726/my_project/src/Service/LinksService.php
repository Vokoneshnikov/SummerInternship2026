<?php

namespace App\Service;

use App\Entity\Links;
use App\Pdo\Response\CreateLinkResponse;
use App\Pdo\Response\GetOriginalLinkResponse;
use App\Repository\LinksRepository;

class LinksService {
    private LinksRepository $linksRepository;
    public function __construct(LinksRepository $linksRepository)
    {
        $this->linksRepository = $linksRepository;
    }
    public function index() : array
    {
        return $this->linksRepository->readAll();
    }
    public function createLink(string $link): string
    {

        $existingLink = $this->linksRepository->findOneBy(['oldLink' => $link]);
        if ($existingLink) {
            return "http://localhost:5000/short/" . $existingLink->getNewLink();
        }

        $newLink = $this->generateLinkId();
        $this->linksRepository->create($link, $newLink);
        return "http://localhost:5000/short/" . $newLink;
    }
    public function getOriginalLink(string $link) : string
    {
        return $this->linksRepository->getOriginalLink($link);
    }
    public function updateLink(string $newLink)
    {
        $this->linksRepository->update($newLink);
    }
    public function deleteLink(int $id)
    {
        $this->linksRepository->delete($id);
    }
    function generateLinkId(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($characters), 0, 10);
    }

}
