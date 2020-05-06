<?php

namespace App\Repository;

use App\Entity\MyEuropeChoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MyEuropeChoiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MyEuropeChoice::class);
    }

    public function createQueryBuilderForStep(string $step): QueryBuilder
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.step = :step')
            ->setParameter('step', MyEuropeChoice::STEPS[$step])
            ->orderBy('c.contentKey', 'ASC')
        ;
    }

    /**
     * @return MyEuropeChoice[]
     */
    public function findByStep(string $step): array
    {
        return $this
            ->createQueryBuilderForStep($step)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMailIntroduction(): ?MyEuropeChoice
    {
        return $this->findOneBy(['contentKey' => MyEuropeChoice::MAIL_INTRODUCTION_KEY]);
    }

    public function findMailCommon(): ?MyEuropeChoice
    {
        return $this->findOneBy(['contentKey' => MyEuropeChoice::MAIL_COMMON_KEY]);
    }

    public function findMailConclusion(): ?MyEuropeChoice
    {
        return $this->findOneBy(['contentKey' => MyEuropeChoice::MAIL_CONCLUSION_KEY]);
    }
}
