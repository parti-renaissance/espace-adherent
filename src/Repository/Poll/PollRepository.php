<?php

declare(strict_types=1);

namespace App\Repository\Poll;

use App\Entity\Poll\Poll;
use App\Entity\Poll\PollResultDisplayModeEnum;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PollRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poll::class);
    }

    public function findActivePollForAlert(): ?Poll
    {
        return $this->createQueryBuilder('poll')
            ->where('poll.published = true')
            ->andWhere('poll.alertEnabled = true')
            ->andWhere('poll.startAt <= :now')
            ->andWhere('poll.finishAt > :now')
            ->orderBy('poll.finishAt', 'ASC')
            ->addOrderBy('poll.id', 'ASC')
            ->setParameter('now', new \DateTimeImmutable())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findConflictingPublishedPoll(Poll $poll): ?Poll
    {
        $queryBuilder = $this->createQueryBuilder('poll')
            ->where('poll.published = true')
            ->andWhere('poll.startAt < :finishAt')
            ->andWhere('poll.finishAt > :startAt')
            ->setParameter('startAt', $poll->getStartAt())
            ->setParameter('finishAt', $poll->getFinishAt())
            ->setMaxResults(1)
        ;

        if (null !== $poll->getId()) {
            $queryBuilder
                ->andWhere('poll.id != :excludedId')
                ->setParameter('excludedId', $poll->getId())
            ;
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findLastActivePoll(): ?Poll
    {
        return $this->createQueryBuilder('poll')
            ->where('poll.published = true')
            ->andWhere('poll.startAt <= :now')
            ->andWhere('(poll.finishAt > :now OR (poll.resultDisplayMode != :never AND COALESCE(poll.resultDisplayEndAt, poll.finishAt) > :now))')
            ->orderBy('poll.finishAt', 'desc')
            ->setParameter('never', PollResultDisplayModeEnum::NEVER)
            ->setParameter('now', new \DateTimeImmutable())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
