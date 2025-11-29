<?php

declare(strict_types=1);

namespace App\Repository\Pap;

use App\Entity\Pap\BuildingEvent;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class BuildingEventRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BuildingEvent::class);
    }

    public function findLastByType(
        string $type,
        UuidInterface $buildingUuid,
        UuidInterface $campaignUuid,
    ): ?BuildingEvent {
        return $this->createQueryBuilder('event')
            ->innerJoin('event.building', 'building')
            ->innerJoin('event.campaign', 'campaign')
            ->where('event.type = :type')
            ->andWhere('building.uuid = :building')
            ->andWhere('campaign.uuid = :campaign')
            ->setParameters([
                'type' => $type,
                'building' => $buildingUuid,
                'campaign' => $campaignUuid,
            ])
            ->orderBy('event.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
