<?php

namespace App\Repository\ElectedRepresentative;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\ElectedRepresentative\Zone;
use App\Entity\ElectedRepresentative\ZoneCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ZoneRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    /**
     * Finds zones for autocomplete.
     */
    public function findForAutocomplete(string $zone, string $type, int $limit = 10): array
    {
        $mandateType = array_search($type, MandateTypeEnum::toArray());
        $categories = ZoneCategory::ZONES[$mandateType];

        if (!\count($categories)) {
            return [];
        }

        if (MandateTypeEnum::SENATOR === $type) {
            return $this->createQueryBuilder('zone')
                ->innerJoin('zone.category', 'category')
                ->where('zone.name LIKE :name')
                ->andWhere('category.name IN (:categories) OR (category.name = :cityCategory AND zone.name LIKE :paris)')
                ->setParameters([
                    'name' => $zone.'%',
                    'categories' => $categories,
                    'cityCategory' => ZoneCategory::CITY,
                    'paris' => '%Paris%75%',
                ])
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult()
            ;
        }

        return $this->createQueryBuilder('zone')
            ->innerJoin('zone.category', 'category')
            ->where('zone.name LIKE :name')
            ->andWhere('category.name IN (:categories)')
            ->setParameters([
                'name' => $zone.'%',
                'categories' => $categories,
            ])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
