<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\AppHit;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\TargetTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class AppHitRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppHit::class);
    }

    public function countImpressionAndOpenStats(TargetTypeEnum $type, UuidInterface $objectUuid): array
    {
        $qb = $this->createQueryBuilder('h')
            ->select('COUNT(DISTINCT IF(h.eventType = :event_type_impression, h.adherent, null)) as unique_impressions')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_impression AND h.source = :source_timeline, h.adherent, null)) as unique_impressions_from_timeline')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open, h.adherent, null)) as unique_opens')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.source = :source_timeline, h.adherent, null)) as unique_opens_from_timeline')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.source = :source_push, h.adherent, null)) as unique_opens_from_notification')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.source = :source_direct_link, h.adherent, null)) as unique_opens_from_direct_link')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_impression AND h.source = :source_list, h.id, null)) as unique_impressions_from_list')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.source = :source_list, h.adherent, null)) as unique_opens_from_list')
            ->where('h.objectId = :object_id')
            ->setParameters([
                'object_id' => $objectUuid,
                'event_type_impression' => EventTypeEnum::Impression,
                'event_type_open' => EventTypeEnum::Open,
                'source_timeline' => 'page_timeline',
                'source_push' => 'push_notification',
                'source_direct_link' => 'direct_link',
                'source_list' => match ($type) {
                    TargetTypeEnum::Event => 'page_events',
                    TargetTypeEnum::Action => 'page_actions',
                    default => null,
                },
            ])
        ;

        return array_merge(array_fill_keys([
            'unique_impressions',
            'unique_impressions_from_list',
            'unique_impressions_from_timeline',
            'unique_opens',
            'unique_opens_from_timeline',
            'unique_opens_from_notification',
            'unique_opens_from_direct_link',
            'unique_opens_from_list',
        ], 0), $qb->getQuery()->getOneOrNullResult() ?? []);
    }

    public function getPaginatedStats(EventTypeEnum $eventType, TargetTypeEnum $targetType, UuidInterface $targetUuid, int $page, int $limit): PaginatorInterface
    {
        $queryBuilder = $this->createQueryBuilder('h')
            ->where('h.eventType = :event_type')
            ->andWhere('h.objectType = :object_type')
            ->andWhere('h.objectId = :object_id')
            ->setParameters([
                'event_type' => $eventType,
                'object_type' => $targetType,
                'object_id' => $targetUuid,
            ])
            ->orderBy('h.createdAt', 'DESC')
        ;

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }
}
