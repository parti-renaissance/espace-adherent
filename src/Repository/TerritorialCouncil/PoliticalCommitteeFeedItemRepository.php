<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PoliticalCommitteeFeedItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PoliticalCommitteeFeedItem::class);
    }

    public function getFeedItems(
        PoliticalCommittee $politicalCommittee,
        int $limit = 30,
        int $firstResultIndex = 0
    ): Paginator {
        $qb = $this->createQueryBuilder('feedItem')
            ->select('feedItem')
            ->addSelect('adherent')
            ->leftJoin('feedItem.author', 'adherent')
            ->where('feedItem.politicalCommittee = :politicalCommittee')
            ->orderBy('feedItem.createdAt', 'DESC')
            ->setParameter('politicalCommittee', $politicalCommittee)
            ->setFirstResult($firstResultIndex)
            ->setMaxResults($limit)
        ;

        return new Paginator($qb);
    }
}
