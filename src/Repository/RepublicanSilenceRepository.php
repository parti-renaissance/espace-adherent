<?php

namespace AppBundle\Repository;

use AppBundle\Entity\RepublicanSilence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RepublicanSilenceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RepublicanSilence::class);
    }

    /**
     * @return RepublicanSilence[]
     */
    public function findStarted(\DateTimeInterface $startDate): iterable
    {
        return $this
            ->createQueryBuilder('silence')
            ->where('silence.beginAt <= :date AND silence.finishAt > :date')
            ->setParameter('date', $startDate)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return RepublicanSilence[]
     */
    public function findActiveBetweenDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate): iterable
    {
        return $this
            ->createQueryBuilder('silence')
            ->where('silence.beginAt <= :end_date AND silence.finishAt >= :start_date')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->getQuery()
            ->getResult()
        ;
    }
}
