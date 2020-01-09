<?php

namespace AppBundle\Repository\Nomenclature;

use AppBundle\Entity\Nomenclature\SenatorArea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SenatorAreaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SenatorArea::class);
    }

    public function findReferentArea(string $code): ?SenatorArea
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * @return SenatorArea[]
     */
    public function findAllGrouped(): array
    {
        $zones = $this
            ->createQueryBuilder('area')
            ->orderBy('area.code', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var $zone SenatorArea */
        foreach ($zones as $zone) {
            $groupedZones[$zone->getTypeLabel()][] = $zone;
        }

        return $groupedZones ?? [];
    }
}
