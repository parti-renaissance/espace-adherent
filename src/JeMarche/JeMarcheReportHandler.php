<?php

namespace AppBundle\JeMarche;

use AppBundle\Entity\JeMarcheReport;
use AppBundle\Mail\Transactional\JeMarcheReportMail;
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

        $this->mailPost->address(
            JeMarcheReportMail::class,
            JeMarcheReportMail::createRecipient($jeMarcheReport),
            null,
            JeMarcheReportMail::createTemplateVars($jeMarcheReport),
            JeMarcheReportMail::SUBJECT
        );
    }
}
