<?php

namespace App\Repository;

use App\Entity\TonMacronChoice;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TonMacronChoiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TonMacronChoice::class);
    }

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
        if (Genders::FEMALE === $gender) {
            return $this->findOneBy(['contentKey' => TonMacronChoice::FEMALE_KEY]);
        }

        return $this->findOneBy(['contentKey' => TonMacronChoice::MALE_KEY]);
    }

    public function findMailConclusion(): ?TonMacronChoice
    {
        return $this->findOneBy(['contentKey' => TonMacronChoice::MAIL_CONCLUSION_KEY]);
    }
}
