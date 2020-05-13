<?php

namespace App\Repository;

use App\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class MailchimpCampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailchimpCampaign::class);
    }
}
