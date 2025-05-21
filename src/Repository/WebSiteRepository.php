<?php

namespace App\Repository;

use App\Entity\WebSite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebSite>
 */
class WebSiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebSite::class);
    }

    public function save(WebSite $webSite, bool $flush = true): void
    {
        $this->getEntityManager()->persist($webSite);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WebSite $webSite, bool $flush = true): void
    {
        $this->getEntityManager()->remove($webSite);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
