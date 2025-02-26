<?php

namespace App\Adherent\Referral;

use App\Entity\Referral;
use Doctrine\ORM\EntityManagerInterface;

class ReportHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Notifier $notifier,
    ) {
    }

    public function report(Referral $referral): void
    {
        $referral->report();

        $this->entityManager->flush();

        $this->notifier->sendReportMessage($referral);
    }
}
