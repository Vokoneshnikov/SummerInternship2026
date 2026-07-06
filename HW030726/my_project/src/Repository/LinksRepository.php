<?php

namespace App\Repository;

use App\Entity\Links;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
//use Doctrine\ORM\$this->getEntityManager()Interface;
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

    public function create(string $fullLink, string $newLink)
    {
        $links = new Links();
        $links->setOldLink($fullLink);
        $links->setCreatedAt(new \DateTimeImmutable());
        $links->setLastUsedAt(new \DateTimeImmutable());
        $links->setUsageCount(0);
        $links->setNewLink($newLink);

        $this->getEntityManager()->persist($links);
        $this->getEntityManager()->flush();
    }
    public function read(string $id)
    {
        return $this->getEntityManager()->getRepository(Links::class)->find($id);
    }
    public function update(string $newLink)
    {
        $link = $this->findOneBy(['newLink' => $newLink]);

        if ($link) {
            $link->setUsageCount($link->getUsageCount() + 1);
            $link->setLastUsedAt(new \DateTimeImmutable());

            $this->getEntityManager()->flush();
        }
    }
    public function delete(int $id)
    {
        $link = $this->getEntityManager()->getRepository(Links::class)->find($id);

        if ($link) {
            $this->getEntityManager()->remove($link);
            $this->getEntityManager()->flush();
        }
    }
    public function readAll() : array
    {
        return $this->findAll();
    }
    public function getOriginalLink(string $newLink) : string
    {
        return $this->findOneBy(['newLink' => $newLink])->getOldLink();
    }
}
