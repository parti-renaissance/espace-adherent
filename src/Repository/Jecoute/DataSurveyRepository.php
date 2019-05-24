<?php

namespace AppBundle\Repository\Jecoute;

use AppBundle\Entity\Jecoute\DataSurvey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DataSurveyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DataSurvey::class);
    }

    public function countByEmailAnsweredForOneMonth(string $email, \DateTime $postedAt): int
    {
        $endDate = clone $postedAt;

        return $this
            ->createQueryBuilder('dataSurvey')
            ->select('COUNT(dataSurvey.id)')
            ->andWhere('dataSurvey.emailAddress = :email')
            ->andWhere('dataSurvey.postedAt >= :startDate')
            ->andWhere('dataSurvey.postedAt < :endDate')
            ->setParameter('email', $email)
            ->setParameter('startDate', $postedAt->modify('-1 month'))
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
