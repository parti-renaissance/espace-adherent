<?php

namespace App\Mailchimp\Synchronisation;

use App\Entity\MailchimpSegment;
use App\Mailchimp\Driver;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class MailchimpSegmentHandler
{
    private $driver;
    private $entityManager;

    public function __construct(Driver $driver, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->driver = $driver;
        $this->entityManager = $entityManager;
    }

    public function synchronize(MailchimpSegment $mailchimpSegment): void
    {
        $this->post($mailchimpSegment);
    }

    private function post(MailchimpSegment $mailchimpSegment): void
    {
        $data = $this->driver->createSegment($mailchimpSegment);

        if (isset($data['id'])) {
            $mailchimpSegment->setExternalId($data['id']);
            $this->entityManager->flush();
        }
    }
}
