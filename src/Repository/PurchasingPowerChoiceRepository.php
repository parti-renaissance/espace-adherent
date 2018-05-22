<?php

namespace AppBundle\Repository;

use AppBundle\Entity\PurchasingPowerChoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PurchasingPowerChoiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PurchasingPowerChoice::class);
    }

    public function createQueryBuilderForStep(string $step): QueryBuilder
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.step = :step')
            ->setParameter('step', PurchasingPowerChoice::STEPS[$step])
            ->orderBy('c.contentKey', 'ASC')
        ;
    }

    /**
     * @return PurchasingPowerChoice[]
     */
    public function findByStep(string $step): array
    {
        return $this
            ->createQueryBuilderForStep($step)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMailIntroduction(): ?PurchasingPowerChoice
    {
        return $this->findOneBy(['contentKey' => PurchasingPowerChoice::MAIL_INTRODUCTION_KEY]);
    }

    public function findMailCommon(): ?PurchasingPowerChoice
    {
        return $this->findOneBy(['contentKey' => PurchasingPowerChoice::MAIL_COMMON_KEY]);
    }

    public function findMailConclusion(): ?PurchasingPowerChoice
    {
        return $this->findOneBy(['contentKey' => PurchasingPowerChoice::MAIL_CONCLUSION_KEY]);
    }
}
