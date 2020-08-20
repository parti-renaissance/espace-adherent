<?php

namespace App\Mailchimp\Synchronisation;

use App\Entity\MailchimpSegment;
use App\Mailchimp\Manager;
use Doctrine\ORM\EntityManagerInterface;

class MailchimpSegmentHandler
{
    private $manager;
    private $entityManager;

    public function __construct(Manager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    public function synchronize(MailchimpSegment $mailchimpSegment): void
    {
        if ($segmentId = $this->manager->createMailchimpSegment($mailchimpSegment)) {
            $mailchimpSegment->setExternalId($segmentId);

            $this->entityManager->flush();
        }
    }
}
