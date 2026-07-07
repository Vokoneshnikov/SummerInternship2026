<?php

namespace App\Service;

use App\Entity\Links;
use App\Entity\User;
use App\Repository\LinksRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LinksService {
    private string $path;
    private LinksRepository $linksRepository;
    public function __construct(LinksRepository $linksRepository, ContainerBagInterface $params)
    {
        $this->linksRepository = $linksRepository;
        $this->path = $params->get('app.path_for_short_urls');
    }
    public function index(User $user) : array
    {
        $links = $this->linksRepository->findBy(['user' => $user]);

        return  array_map(function ($link) {
            return [
                'id' => $link->getId(),
                'oldLink' => $link->getOldLink(),
                'newLink' => $link->getNewLink(),
                'createdAt' => $link->getCreatedAt(),
                'lastUsedAt' => $link->getLastUsedAt(),
                'usageCount' => $link->getUsageCount(),
            ];
        }, $links);
    }
    public function createLink(Links $link, User $user): string
    {
        $existingLink = $this->linksRepository->findOneBy(['oldLink' => $link->getOldLink()]);
        if ($existingLink) {
            return $this->path . $existingLink->getNewLink();
        }

        $newLink = $this->generateLinkId();

        $link->setNewLink($newLink);
        $link->setCreatedAt(new \DateTimeImmutable());
        $link->setLastUsedAt(new \DateTimeImmutable());
        $link->setUsageCount(0);
        $link->setUser($user);

        $this->linksRepository->create($link);
        return $this->path . $newLink;
    }
    public function deleteLink(Links $link, User $user)
    {
        if ($link->getUser()->getId() === $user->getId()) {
            $this->linksRepository->delete($link);
        }
        else {
            throw new
            AccessDeniedException(
                'Вы не можете удалить эту ссылку'
            );
        }
    }
    public function processRedirect(string $newLink) : ?string
    {
        $link = $this->linksRepository->findByNewLink($newLink);

        if (!$link) {
            return null;
        }
        if ($this->isExpired($link)) {
            $this->linksRepository->delete($link);
            return null;
        }
        if ($link->isDisposable()) {

            $this->linksRepository->delete($link);
            return $link->getOldLink();
        }
        $this->linksRepository->update($link);
        return $link->getOldLink();

    }
    function generateLinkId(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        while (true) {
            $randomString = substr(str_shuffle($characters), 0, 10);

            $exists = $this->linksRepository->findOneBy(['newLink' => $randomString]);
            if ($exists) {
                continue;
            }
            break;
        }
        return $randomString;
    }
    function isExpired(Links $link) : bool
    {
        if (!$link->getExpiresAt()) {
            return false;
        }
        return $link->getExpiresAt() < new \DateTimeImmutable();
    }

}
