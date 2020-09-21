<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilFeedItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TerritorialCouncilFeedItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TerritorialCouncilFeedItem::class);
    }

    public function getFeedItems(
        TerritorialCouncil $territorialCouncil,
        int $limit = 30,
        int $firstResultIndex = 0
    ): Paginator {
        $qb = $this->createQueryBuilder('feedItem')
            ->select('feedItem')
            ->addSelect('adherent')
            ->leftJoin('feedItem.author', 'adherent')
            ->where('feedItem.territorialCouncil = :territorialCouncil')
            ->orderBy('feedItem.createdAt', 'DESC')
            ->setParameter('territorialCouncil', $territorialCouncil)
            ->setFirstResult($firstResultIndex)
            ->setMaxResults($limit)
        ;

        return new Paginator($qb);
    }
}
