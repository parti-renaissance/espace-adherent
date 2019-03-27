<?php

namespace AppBundle\Repository;

use AppBundle\Entity\AdherentReferentData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AdherentReferentDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AdherentReferentData::class);
    }

    public function findTagsGroupByCategory(): array
    {
        $tags = $this
            ->createQueryBuilder('ard')
            ->select(['tag.id', 'tag.name', 'tag.code', 'tag.category'])
            ->innerJoin('ard.tags', 'tag')
            ->orderBy('tag.code', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        /** @var $zone ReferentArea */
        foreach ($tags as $tag) {
            $groupedZones[$tag['category']][] = $tag;
        }

        return $groupedZones ?? [];
    }
}
