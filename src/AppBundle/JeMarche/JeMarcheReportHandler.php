<?php

namespace AppBundle\JeMarche;

use AppBundle\Entity\JeMarcheReport;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\JeMarcheReportMessage;
use Doctrine\Common\Persistence\ObjectManager;

class JeMarcheReportHandler
{
    private $entityManager;
    private $mailjet;

    public function __construct(ObjectManager $entityManager, MailjetService $mailjet)
    {
        $this->entityManager = $entityManager;
        $this->mailjet = $mailjet;
    }

    public function handle(JeMarcheReport $jeMarcheReport)
    {
        $this->entityManager->persist($jeMarcheReport);
        $this->entityManager->flush();

        $this->mailjet->sendMessage(JeMarcheReportMessage::createFromJeMarcheReport($jeMarcheReport));
    }
}
