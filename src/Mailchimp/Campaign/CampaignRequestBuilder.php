<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Request\EditCampaignRequest;

class CampaignRequestBuilder
{
    private $objectIdMapping;
    private $segmentConditionsBuilder;

    public function __construct(
        MailchimpObjectIdMapping $objectIdMapping,
        SegmentConditionsBuilder $segmentConditionsBuilder,
    ) {
        $this->objectIdMapping = $objectIdMapping;
        $this->segmentConditionsBuilder = $segmentConditionsBuilder;
    }

    public function createEditCampaignRequestFromMessage(MailchimpCampaign $campaign): EditCampaignRequest
    {
        $message = $campaign->getMessage();

        return new EditCampaignRequest()
            ->setFolderId($this->objectIdMapping->getFolderIdByType($message->getInstanceScope() ?? ''))
            ->setTemplateId($this->objectIdMapping->getTemplateId($message))
            ->setSubject($message->getSubject())
            ->setTitle($this->createCampaignLabel($campaign))
            ->setSegmentOptions($message->getFilter() ? $this->segmentConditionsBuilder->buildFromMailchimpCampaign($campaign) : [])
            ->setFromName($message->getFromName())
            ->setReplyTo('contact@parti-renaissance.fr')
        ;
    }

    private function createCampaignLabel(MailchimpCampaign $campaign): string
    {
        $message = $campaign->getMessage();

        return implode(' - ', array_filter([
            new \DateTime()->format('Y/m/d'),
            $message->senderInstance,
            ($message->senderName ?? $message->getSender()?->getFullName()).' : '.($message->getSubject() ?: '(Sans sujet)'),
        ]));
    }
}
