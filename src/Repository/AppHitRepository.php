<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\AppHit;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\SourceGroupEnum;
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
            // Impressions
            ->select('COUNT(DISTINCT IF(h.eventType = :event_type_impression, h.adherent, null)) as unique_impressions')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_impression AND h.source = :source_timeline, h.adherent, null)) as unique_impressions__timeline')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_impression AND h.source = :source_list, h.id, null)) as unique_impressions__list')

            // Opens
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open, h.adherent, null)) as unique_opens')
            ->addSelect('COALESCE(CAST(ROUND(COUNT(DISTINCT IF(h.eventType = :event_type_open, h.adherent, null)) * 100.0 / NULLIF(COUNT(DISTINCT IF(h.eventType = :event_type_impression, h.adherent, null)), 0), 2) AS FLOAT), 0.0) as unique_opens__total_rate')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.source = :source_timeline, h.adherent, null)) as unique_opens__timeline')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.source = :source_push, h.adherent, null)) as unique_opens__notification')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.source = :source_direct_link, h.adherent, null)) as unique_opens__direct_link')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.source = :source_list, h.adherent, null)) as unique_opens__list')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.source = :source_email, h.adherent, null)) as unique_opens__email')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.sourceGroup = :source_group_app, h.adherent, null)) as unique_opens__app')

            // Clicks
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_click, h.adherent, null)) as unique_clicks')
            ->addSelect('COALESCE(CAST(ROUND(COUNT(DISTINCT IF(h.eventType = :event_type_click, h.adherent, null)) * 100.0 / NULLIF(COUNT(DISTINCT IF(h.eventType = :event_type_open, h.adherent, null)), 0), 2) AS FLOAT), 0.0) as unique_clicks__total_rate')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_click AND h.source = :source_email, h.adherent, null)) as unique_clicks__email')
            ->addSelect('COALESCE(CAST(ROUND(COUNT(DISTINCT IF(h.eventType = :event_type_click AND h.source = :source_email, h.adherent, null)) * 100.0 / NULLIF(COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.source = :source_email, h.adherent, null)), 0), 2) AS FLOAT), 0.0) as unique_clicks__email_rate')
            ->addSelect('COUNT(DISTINCT IF(h.eventType = :event_type_click AND h.sourceGroup = :source_group_app, h.adherent, null)) as unique_clicks__app')
            ->addSelect('COALESCE(CAST(ROUND(COUNT(DISTINCT IF(h.eventType = :event_type_click AND h.sourceGroup = :source_group_app, h.adherent, null)) * 100.0 / NULLIF(COUNT(DISTINCT IF(h.eventType = :event_type_open AND h.sourceGroup = :source_group_app, h.adherent, null)), 0), 2) AS FLOAT), 0.0) AS unique_clicks__app_rate')

            ->where('h.objectId = :object_id')
            ->setParameters([
                'object_id' => $objectUuid,
                'event_type_impression' => EventTypeEnum::Impression,
                'event_type_open' => EventTypeEnum::Open,
                'event_type_click' => EventTypeEnum::Click,
                'source_group_app' => SourceGroupEnum::App,
                'source_timeline' => 'page_timeline',
                'source_push' => 'push_notification',
                'source_direct_link' => 'direct_link',
                'source_email' => 'email',
                'source_list' => match ($type) {
                    TargetTypeEnum::Event => 'page_events',
                    TargetTypeEnum::Action => 'page_actions',
                    default => null,
                },
            ])
        ;

        return array_merge(array_fill_keys([
            'unique_impressions',
            'unique_impressions__list',
            'unique_impressions__timeline',
            'unique_opens',
            'unique_opens__total_rate',
            'unique_opens__timeline',
            'unique_opens__notification',
            'unique_opens__direct_link',
            'unique_opens__list',
            'unique_opens__email',
            'unique_opens__app',
            'unique_clicks',
            'unique_clicks__total_rate',
            'unique_clicks__email',
            'unique_clicks__email_rate',
            'unique_clicks__app',
            'unique_clicks__app_rate',
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
