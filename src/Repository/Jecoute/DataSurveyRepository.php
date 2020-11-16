<?php

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\Survey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
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

    public function iterateForSurvey(Survey $survey): IterableResult
    {
        return $this->createQueryBuilder('jds')
            ->where('jds.survey = :survey')
            ->setParameter('survey', $survey)
            ->getQuery()
            ->iterate()
        ;
    }
}
