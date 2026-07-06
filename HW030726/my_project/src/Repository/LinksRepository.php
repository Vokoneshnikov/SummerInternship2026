<?php

namespace App\Repository;

use App\Entity\Links;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Links>
 */
class LinksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Links::class);
    }

    public function create(string $fullLink, string $shortLink)
    {
        $links = new Links();
        $links->setOldLink($fullLink);
        $links->setCreatedAt(new \DateTime());
        $links->setUsageCount(0);
        $links->setLastUsedAt(new \DateTime());
        $links->setNewLink($shortLink);

        $entityManager->persist($links);
        $entityManager->flush();
    }
    public function read(Links $links)
    {

    }
    public function update(string $id)
    {

    }
    public function delete(string $id)
    {

    }
    public function readAll() : array
    {
        return $this->findAll();
    }
    public function getOriginalLink(string $shortLink) : string
    {
        return $this->findOneBy(['newLink' => $shortLink]);
    }
}
