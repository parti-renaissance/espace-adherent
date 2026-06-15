<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\MandrillFallbackStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MailchimpCampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailchimpCampaign::class);
    }

    public function claimMandrillFallback(int $campaignId): bool
    {
        $affected = (int) $this->createQueryBuilder('c')
            ->update()
            ->set('c.mandrillFallbackStatus', ':attempted')
            ->where('c.id = :id')
            ->andWhere('c.mandrillFallbackStatus IS NULL')
            ->setParameter('attempted', MandrillFallbackStatusEnum::Attempted)
            ->setParameter('id', $campaignId)
            ->getQuery()
            ->execute()
        ;

        return 1 === $affected;
    }
}
