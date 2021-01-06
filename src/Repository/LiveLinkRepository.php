<?php

namespace App\Repository;

use App\Entity\LiveLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LiveLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LiveLink::class);
    }

    /**
     * @return LiveLink[]
     */
    public function findHomeLiveLinks()
    {
        return $this->findBy([], ['position' => 'ASC']);
    }
}
