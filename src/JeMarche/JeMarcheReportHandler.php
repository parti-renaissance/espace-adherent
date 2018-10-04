<?php

namespace AppBundle\JeMarche;

use AppBundle\Entity\JeMarcheReport;
use AppBundle\Mail\Transactional\JeMarcheReportMail;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\JeMarcheReportMessage;
use Doctrine\Common\Persistence\ObjectManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class JeMarcheReportHandler
{
    private $entityManager;
    private $mailPost;

    public function __construct(ObjectManager $entityManager, MailPostInterface $mailPost)
    {
        $this->entityManager = $entityManager;
        $this->mailPost = $mailPost;
    }

    public function handle(JeMarcheReport $jeMarcheReport)
    {
        $this->entityManager->persist($jeMarcheReport);
        $this->entityManager->flush();

        $this->mailPost->sendMessage(JeMarcheReportMessage::createFromJeMarcheReport($jeMarcheReport));

        $this->mailPost->address(
            JeMarcheReportMail::class,
            JeMarcheReportMail::createRecipientFor($jeMarcheReport),
            null,
            JeMarcheReportMail::createTemplateVarsFrom($jeMarcheReport),
            JeMarcheReportMail::SUBJECT
        );
    }
}
