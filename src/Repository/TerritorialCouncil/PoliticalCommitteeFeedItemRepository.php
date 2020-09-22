<?php

namespace App\Repository\TerritorialCouncil;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use App\Repository\AuthorTrait;
use App\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PoliticalCommitteeFeedItemRepository extends ServiceEntityRepository
{
    use AuthorTrait;
    use PaginatorTrait;

    public function __construct(RegistryInterface $registry)
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
