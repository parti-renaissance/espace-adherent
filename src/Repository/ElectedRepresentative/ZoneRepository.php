<?php

namespace AppBundle\Repository\ElectedRepresentative;

use AppBundle\Entity\ElectedRepresentative\MandateTypeEnum;
use AppBundle\Entity\ElectedRepresentative\Zone;
use AppBundle\Entity\ElectedRepresentative\ZoneCategory;
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
