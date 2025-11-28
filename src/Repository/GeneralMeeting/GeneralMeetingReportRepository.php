<?php

declare(strict_types=1);

namespace App\Repository\GeneralMeeting;

use App\Entity\GeneralMeeting\GeneralMeetingReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GeneralMeetingReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeneralMeetingReport::class);
    }
}
