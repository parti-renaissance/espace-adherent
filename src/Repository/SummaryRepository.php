<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Summary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SummaryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Summary::class);
    }

    public function createQueryBuilderForAdherent(Adherent $adherent): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->where('s.member = :member')
            ->setParameter('member', $adherent)
        ;
    }

    public function findOneForAdherent(Adherent $adherent): ?Summary
    {
        return $this->createQueryBuilderForAdherent($adherent)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneBySlug(string $slug): ?Summary
    {
        return $this->createQueryBuilder('s')
            ->select('s', 'm', 'mt', 'e', 'sk', 'l', 't')
            ->leftJoin('s.member', 'm')
            ->leftJoin('s.missionTypeWishes', 'mt')
            ->leftJoin('s.experiences', 'e')
            ->leftJoin('s.skills', 'sk')
            ->leftJoin('s.languages', 'l')
            ->leftJoin('s.trainings', 't')
            ->where('s.slug = :slug')
            ->andWhere('s.public = :public')
            ->setParameter('slug', $slug)
            ->setParameter('public', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
