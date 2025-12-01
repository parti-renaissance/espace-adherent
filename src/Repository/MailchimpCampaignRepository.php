<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\AdherentMessage\MailchimpCampaign>
 */
class MailchimpCampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailchimpCampaign::class);
    }
}
