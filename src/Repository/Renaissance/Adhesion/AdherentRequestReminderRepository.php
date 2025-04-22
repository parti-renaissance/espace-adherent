<?php

namespace App\Repository\Renaissance\Adhesion;

use App\Adhesion\AdherentRequestReminderTypeEnum;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Entity\Renaissance\Adhesion\AdherentRequestReminder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentRequestReminderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentRequestReminder::class);
    }

    public function hasBeenReminded(AdherentRequest $adherentRequest, AdherentRequestReminderTypeEnum $type): bool
    {
        return 0 !== $this->count(['adherentRequest' => $adherentRequest, 'type' => $type]);
    }
}
