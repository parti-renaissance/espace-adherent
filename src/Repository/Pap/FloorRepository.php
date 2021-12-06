<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Building;
use App\Entity\Pap\Floor;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FloorRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Floor::class);
    }

    public function findOneInBuilding(Building $building, string $buildingBlock, int $floor): ?Floor
    {
        return $this->createQueryBuilder('floor')
            ->where('floor.number = :number')
            ->andWhere('buildingBlock.name = :building_block AND buildingBlock.building = :building')
            ->innerJoin('floor.buildingBlock', 'buildingBlock')
            ->setParameters([
                'building' => $building,
                'building_block' => $buildingBlock,
                'number' => $floor,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
