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
            ->andWhere('poll.alertDisabled = false')
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

    public function countConflictingPolls(\DateTimeInterface $lowerBound, \DateTimeInterface $upperBound, ?int $excludedId = null): int
    {
        $queryBuilder = $this->createQueryBuilder('poll')
            ->select('COUNT(poll.id)')
            ->where('poll.finishAt > :lowerBound')
            ->andWhere('poll.startAt < :upperBound')
            ->setParameter('lowerBound', $lowerBound)
            ->setParameter('upperBound', $upperBound)
        ;

        if (null !== $excludedId) {
            $queryBuilder
                ->andWhere('poll.id != :excludedId')
                ->setParameter('excludedId', $excludedId)
            ;
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
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
