<?php

declare(strict_types=1);

namespace App\Repository\Poll;

use App\Entity\Adherent;
use App\Entity\Poll\Poll;
use App\Entity\Poll\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function hasVoted(Poll $poll, Adherent $adherent): bool
    {
        return (bool) $this->createQueryBuilder('vote')
            ->select('COUNT(vote.id)')
            ->where('vote.poll = :poll')
            ->andWhere('vote.adherent = :adherent')
            ->setParameter('poll', $poll)
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return Adherent[]
     */
    public function findLatestVotersWithImage(Poll $poll, int $limit = 5): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('adherent')
            ->from(Adherent::class, 'adherent')
            ->innerJoin(Vote::class, 'vote', Join::WITH, 'vote.adherent = adherent')
            ->where('vote.poll = :poll')
            ->andWhere('adherent.imageName IS NOT NULL')
            ->orderBy('vote.createdAt', 'DESC')
            ->setParameter('poll', $poll)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
