<?php

declare(strict_types=1);

namespace App\Repository\Pap;

use App\Entity\Pap\BuildingEvent;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Pap\BuildingEvent>
 */
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
            ->setParameters(new ArrayCollection([new Parameter('type', $type), new Parameter('building', $buildingUuid), new Parameter('campaign', $campaignUuid)]))
            ->orderBy('event.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
