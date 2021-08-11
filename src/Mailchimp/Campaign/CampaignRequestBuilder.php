<?php

namespace App\Mailchimp\Campaign;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Request\EditCampaignRequest;

class CampaignRequestBuilder
{
    private $objectIdMapping;
    private $segmentConditionsBuilder;

    public function __construct(
        MailchimpObjectIdMapping $objectIdMapping,
        SegmentConditionsBuilder $segmentConditionsBuilder
    ) {
        $this->objectIdMapping = $objectIdMapping;
        $this->segmentConditionsBuilder = $segmentConditionsBuilder;
    }

    public function createEditCampaignRequestFromMessage(MailchimpCampaign $campaign): EditCampaignRequest
    {
        $message = $campaign->getMessage();

        return (new EditCampaignRequest())
            ->setFolderId($this->objectIdMapping->getFolderIdByType($message->getType()))
            ->setTemplateId($this->objectIdMapping->getTemplateId($message))
            ->setSubject($message->getSubject())
            ->setTitle($this->createCampaignLabel($campaign))
            ->setSegmentOptions($message->getFilter() ? $this->segmentConditionsBuilder->buildFromMailchimpCampaign($campaign) : [])
            ->setFromName($message->getFromName())
        ;
    }

    private function createCampaignLabel(MailchimpCampaign $campaign): string
    {
        return implode(' - ', array_merge([
            $campaign->getMessage()->getAuthor()->getFullName(),
            (new \DateTime())->format('d/m/Y'),
        ], ($label = $campaign->getLabel()) ? [$label] : []));
    }
}
