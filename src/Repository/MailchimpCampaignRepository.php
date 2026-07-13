<?php

declare(strict_types=1);

namespace App\Repository;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MailchimpCampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailchimpCampaign::class);
    }

    public function reopenForSending(int $campaignId): bool
    {
        $affected = (int) $this->createQueryBuilder('c')
            ->update()
            ->set('c.status', ':sending')
            ->where('c.id = :id')
            ->andWhere('c.status = :sent')
            ->setParameter('sending', MailchimpStatusEnum::Sending)
            ->setParameter('sent', MailchimpStatusEnum::Sent)
            ->setParameter('id', $campaignId)
            ->getQuery()
            ->execute()
        ;

        return 1 === $affected;
    }

    /**
     * Atomically moves a campaign into Sending so the SES orchestrator can fan out exactly once.
     * Returns true only for the caller that wins the transition; a redelivered TriggerSesCampaignMessage
     * (campaign already Sending or Sent) gets false and must abort to avoid a double fan-out.
     */
    public function claimForSending(int $campaignId): bool
    {
        $affected = (int) $this->createQueryBuilder('c')
            ->update()
            ->set('c.status', ':sending')
            ->where('c.id = :id')
            ->andWhere('c.status != :sending')
            ->andWhere('c.status != :sent')
            ->setParameter('sending', MailchimpStatusEnum::Sending)
            ->setParameter('sent', MailchimpStatusEnum::Sent)
            ->setParameter('id', $campaignId)
            ->getQuery()
            ->execute()
        ;

        return 1 === $affected;
    }

    /**
     * Atomically completes a campaign (Sending -> Sent). Guarded on Sending so that when two chunks
     * finish concurrently only one wins the transition — the winner records the reach exactly once.
     */
    public function completeSending(int $campaignId): bool
    {
        $affected = (int) $this->createQueryBuilder('c')
            ->update()
            ->set('c.status', ':sent')
            ->where('c.id = :id')
            ->andWhere('c.status = :sending')
            ->setParameter('sent', MailchimpStatusEnum::Sent)
            ->setParameter('sending', MailchimpStatusEnum::Sending)
            ->setParameter('id', $campaignId)
            ->getQuery()
            ->execute()
        ;

        return 1 === $affected;
    }
}
