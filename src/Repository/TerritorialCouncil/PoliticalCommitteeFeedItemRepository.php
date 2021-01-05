<?php

namespace App\Repository\TerritorialCouncil;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use App\Repository\AuthorTrait;
use App\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PoliticalCommitteeFeedItemRepository extends ServiceEntityRepository
{
    use AuthorTrait;
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PoliticalCommitteeFeedItem::class);
    }

    public function getFeedItems(
        PoliticalCommittee $politicalCommittee,
        int $page = 1,
        int $limit = 30
    ): PaginatorInterface {
        $qb = $this->createQueryBuilder('feedItem')
            ->select('feedItem')
            ->addSelect('adherent')
            ->leftJoin('feedItem.author', 'adherent')
            ->where('feedItem.politicalCommittee = :politicalCommittee')
            ->orderBy('feedItem.createdAt', 'DESC')
            ->setParameter('politicalCommittee', $politicalCommittee)
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }
}
