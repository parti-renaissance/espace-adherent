<?php

namespace AppBundle\Repository;

use AppBundle\Entity\InteractiveChoice;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class InteractiveChoiceRepository extends EntityRepository
{
    public function createQueryBuilderForStep(array $params): QueryBuilder
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.step = :step')
            ->andWhere('c.interactive = :interactive')
            ->setParameter('step', InteractiveChoice::STEPS[$params['step']])
            ->setParameter('interactive', $params['interactive'])
            ->orderBy('c.contentKey', 'ASC')
        ;
    }

    /**
     * @return InteractiveChoice[]
     */
    public function findByStep(string $step): array
    {
        return $this
            ->createQueryBuilderForStep($step)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMailIntroduction(): ?InteractiveChoice
    {
        return $this->findOneBy(['contentKey' => InteractiveChoice::MAIL_INTRODUCTION_KEY]);
    }

    public function findMailCommon(): ?InteractiveChoice
    {
        return $this->findOneBy(['contentKey' => InteractiveChoice::MAIL_COMMON_KEY]);
    }

    public function findMailConclusion(): ?InteractiveChoice
    {
        return $this->findOneBy(['contentKey' => InteractiveChoice::MAIL_CONCLUSION_KEY]);
    }
}
