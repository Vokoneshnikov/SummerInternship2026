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

    public function create(Links $link)
    {
        $this->getEntityManager()->persist($link);
        $this->getEntityManager()->flush();
    }
    public function read(string $id)
    {
        return $this->getEntityManager()->getRepository(Links::class)->find($id);
    }
    public function update(Links $link)
    {
        $link->setUsageCount($link->getUsageCount() + 1);
        $link->setLastUsedAt(new \DateTimeImmutable());

        $this->getEntityManager()->flush();
    }
    public function delete(Links $link)
    {
        $this->getEntityManager()->remove($link);
        $this->getEntityManager()->flush();
    }
    public function readAll() : array
    {
        return $this->findAll();
    }
    public function getOriginalLink(Links $link) : string
    {
        return $this->findOneBy(['newLink' => $link->getNewLink()])->getOldLink();
    }
    public function findByNewLink(string $newLink): ?Links
    {
        return $this->findOneBy(['newLink' => $newLink]);
    }
}
