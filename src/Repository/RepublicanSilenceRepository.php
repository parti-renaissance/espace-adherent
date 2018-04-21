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

    public function findStarted(\DateTime $startDate): iterable
    {
        return $this
            ->createQueryBuilder('silence')
            ->where('silence.beginAt <= :date AND silence.finishAt > :date')
            ->setParameter('date', $startDate)
            ->getQuery()
            ->getResult()
        ;
    }
}
