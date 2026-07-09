<?php

declare(strict_types=1);

namespace App\Repository\Poll;

use App\Entity\Adherent;
use App\Entity\Poll\Poll;
use App\Entity\Poll\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function hasVoted(Poll $poll, Adherent $adherent): bool
    {
        return null !== $this->findAdherentVote($poll->getUuid(), $adherent);
    }

    public function findAdherentVote(Uuid $pollUuid, Adherent $adherent): ?Vote
    {
        return $this->createQueryBuilder('vote')
            ->innerJoin('vote.poll', 'poll')
            ->where('poll.uuid = :poll_uuid')
            ->andWhere('vote.adherent = :adherent')
            ->setParameter('poll_uuid', $pollUuid)
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Adherent[]
     */
    public function findLatestVotersWithImage(Poll $poll, int $limit = 3): array
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
