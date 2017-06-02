<?php

namespace AppBundle\Repository;

use AppBundle\Entity\TonMacronChoice;
use AppBundle\ValueObject\Genders;
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
            ->orderBy('c.contentKey', 'ASC')
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

    public function findGenderChoice(?string $gender): ?TonMacronChoice
    {
        if ($gender === Genders::FEMALE) {
            return $this->findOneBy(['contentKey' => TonMacronChoice::FEMALE_KEY]);
        }

        return $this->findOneBy(['contentKey' => TonMacronChoice::MALE_KEY]);
    }

    public function findMailConclusion(): ?TonMacronChoice
    {
        return $this->findOneBy(['contentKey' => TonMacronChoice::MAIL_CONCLUSION_KEY]);
    }
}
