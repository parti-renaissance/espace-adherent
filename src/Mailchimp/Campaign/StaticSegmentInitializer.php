<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use Doctrine\ORM\EntityManagerInterface;

class StaticSegmentInitializer
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function ensureLocalSegment(MailchimpCampaign $campaign): void
    {
        if (null !== $campaign->getMailchimpStaticSegment()) {
            return;
        }

        $segment = new MailchimpStaticSegment($campaign);
        $segment->name = \sprintf('PROD_%s', $campaign->getMessage()->getUuid()->toRfc4122());

        $campaign->setMailchimpStaticSegment($segment);

        $this->entityManager->persist($segment);
        $this->entityManager->flush();
    }
}
