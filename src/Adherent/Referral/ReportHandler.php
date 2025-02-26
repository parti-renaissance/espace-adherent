<?php

namespace App\Adherent\Referral;

use App\Entity\Referral;
use Doctrine\ORM\EntityManagerInterface;

class ReportHandler
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function report(Referral $referral): void
    {
        $referral->report();

        $this->entityManager->flush();
    }
}
