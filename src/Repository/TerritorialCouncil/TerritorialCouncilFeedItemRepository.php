<?php

namespace App\Repository\TerritorialCouncil;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilFeedItem;
use App\Repository\AuthorTrait;
use App\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TerritorialCouncilFeedItemRepository extends ServiceEntityRepository
{
    use AuthorTrait;
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TerritorialCouncilFeedItem::class);
    }

    public function getFeedItems(
        TerritorialCouncil $territorialCouncil,
        int $page = 1,
        int $limit = 30
    ): PaginatorInterface {
        $qb = $this->createQueryBuilder('feedItem')
            ->addSelect('adherent')
            ->leftJoin('feedItem.author', 'adherent')
            ->where('feedItem.territorialCouncil = :territorialCouncil')
            ->orderBy('feedItem.createdAt', 'DESC')
            ->setParameter('territorialCouncil', $territorialCouncil)
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }
}
