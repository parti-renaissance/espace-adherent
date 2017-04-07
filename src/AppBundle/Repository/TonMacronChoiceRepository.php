<?php

namespace AppBundle\Repository;

use AppBundle\Entity\TonMacronChoice;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class TonMacronChoiceRepository extends EntityRepository
{
    public function createQueryBuilderForStep(string $step): QueryBuilder
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.step = :step')
            ->setParameter('step', TonMacronChoice::STEPS[$step])
        ;
    }

    /**
     * @return TonMacronChoice[]
     */
    public function findByStep(string $step): array
    {
        return $this
            ->createQueryBuilderForStep($step)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMailIntroduction(): ?TonMacronChoice
    {
        return $this->findOneBy(['contentKey' => TonMacronChoice::MAIL_INTRODUCTION_KEY]);
    }

    public function findMailConclusion(): ?TonMacronChoice
    {
        return $this->findOneBy(['contentKey' => TonMacronChoice::MAIL_CONCLUSION_KEY]);
    }
}
