<?php

declare(strict_types=1);

namespace App\Repository\Poll;

use App\Entity\Adherent;
use App\Entity\Poll\Participant;
use App\Entity\Poll\Poll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    public function countForPoll(Poll $poll): int
    {
        return (int) $this->createQueryBuilder('participant')
            ->select('COUNT(participant.id)')
            ->where('participant.poll = :poll')
            ->setParameter('poll', $poll)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return Participant[]
     */
    public function findLatestWithImage(Poll $poll, int $limit = 5): array
    {
        return $this->createQueryBuilder('participant')
            ->innerJoin('participant.adherent', 'adherent')
            ->where('participant.poll = :poll')
            ->andWhere('adherent.imageName IS NOT NULL')
            ->orderBy('participant.createdAt', 'DESC')
            ->setParameter('poll', $poll)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function existsForPollAndAdherent(Poll $poll, Adherent $adherent): bool
    {
        return null !== $this->findOneBy(['poll' => $poll, 'adherent' => $adherent]);
    }
}
