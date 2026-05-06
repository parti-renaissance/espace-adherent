<?php

declare(strict_types=1);

namespace App\Repository;

use App\AdherentMessage\AdherentMessageStatusEnum;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MailchimpCampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailchimpCampaign::class);
    }

    /**
     * Returns campaigns whose Mailchimp static segment can be deleted: message
     * sent more than `$retentionDays` days ago and `staticSegmentId` still set.
     *
     * @return MailchimpCampaign[]
     */
    public function findExpiredForCleanup(int $retentionDays = 7): array
    {
        $threshold = new \DateTimeImmutable()->modify(\sprintf('-%d days', $retentionDays));

        return $this->createQueryBuilder('c')
            ->innerJoin('c.message', 'm')
            ->andWhere('c.staticSegmentId IS NOT NULL')
            ->andWhere('m.status = :sent')
            ->andWhere('m.sentAt <= :threshold')
            ->setParameter('sent', AdherentMessageStatusEnum::SENT)
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();
    }

    /**
     * True if a `MailchimpCampaign` references this `$segmentId` and its
     * preparation is still active (Preparing/Ready). Used by the orphan-segment
     * cleanup.
     */
    public function isLinkedToActiveCampaign(int $segmentId): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->select('1')
            ->andWhere('c.staticSegmentId = :sid')
            ->andWhere('c.preparationStatus IN (:active)')
            ->setParameter('sid', $segmentId)
            ->setParameter('active', [PreparationStatusEnum::Preparing, PreparationStatusEnum::Ready])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
