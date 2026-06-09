<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MailchimpCampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailchimpCampaign::class);
    }

    /**
     * Atomically claims the single zero-delivery recovery attempt for a campaign. Returns true only
     * for the worker that wins the claim (recovery_attempted_at was still NULL), guaranteeing a
     * single replication even under concurrent re-deliveries of the verify command.
     */
    public function tryClaimRecovery(int $campaignId): bool
    {
        // Conditional bulk UPDATE: a single atomic SQL statement at DB level, so only one worker can
        // flip recovery_attempted_at from NULL even under concurrent re-deliveries. execute() returns
        // the affected-row count (1 = this worker won the claim, 0 = already claimed).
        $affected = $this->createQueryBuilder('c')
            ->update()
            ->set('c.recoveryAttemptedAt', ':now')
            ->where('c.id = :id')
            ->andWhere('c.recoveryAttemptedAt IS NULL')
            ->setParameter('now', new \DateTime())
            ->setParameter('id', $campaignId)
            ->getQuery()
            ->execute()
        ;

        return 1 === $affected;
    }
}
