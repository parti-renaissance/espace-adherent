<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Jecoute\Survey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SurveyRepository extends ServiceEntityRepository
{
    use ReferentTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Survey::class);
    }

    /**
     * @return Survey[]
     */
    public function findAllPublished(): array
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect('questions')
            ->innerJoin('survey.questions', 'questions')
            ->andWhere('survey.published = true')
            ->getQuery()
            ->getResult()
         ;
    }

    /**
     * @return Survey[]
     */
    public function findAllPublishedByCreator(Adherent $creator): array
    {
        $this->checkReferent($creator);

        return $this
            ->createQueryBuilder('survey')
            ->addSelect('questions')
            ->innerJoin('survey.questions', 'questions')
            ->andWhere('survey.published = true')
            ->andWhere('survey.creator = :creator')
            ->setParameter('creator', $creator)
            ->getQuery()
            ->getResult()
        ;
    }
}
