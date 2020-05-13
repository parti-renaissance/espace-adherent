<?php

namespace App\JeMarche;

use App\Entity\JeMarcheReport;
use App\Mailer\MailerService;
use App\Mailer\Message\JeMarcheReportMessage;
use Doctrine\Common\Persistence\ObjectManager;

class JeMarcheReportHandler
{
    private $entityManager;
    private $mailer;

    public function __construct(ObjectManager $entityManager, MailerService $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    public function handle(JeMarcheReport $jeMarcheReport)
    {
        $this->entityManager->persist($jeMarcheReport);
        $this->entityManager->flush();

        $this->mailer->sendMessage(JeMarcheReportMessage::createFromJeMarcheReport($jeMarcheReport));
    }
}
