<?php

namespace AppBundle\Repository;

use AppBundle\Entity\PurchasingPowerChoice;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class PurchasingPowerChoiceRepository extends EntityRepository
{
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
