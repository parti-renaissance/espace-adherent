<?php

namespace App\Repository\ElectedRepresentative;

use App\Entity\ElectedRepresentative\Zone;
use App\Entity\ElectedRepresentative\ZoneCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ZoneRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    public function createSelectByReferentTagsQueryBuilder(array $referentTags): QueryBuilder
    {
        return $this->createQueryBuilder('zone')
            ->leftJoin('zone.category', 'category')
            ->leftJoin('zone.referentTags', 'referentTag')
            ->where('category.name = :category')
            ->andWhere('referentTag IN (:tags)')
            ->setParameters([
                'tags' => $referentTags,
                'category' => ZoneCategory::CITY,
            ])
            ->orderBy('zone.name')
        ;
    }
}
