<?php

namespace AppBundle\Mailchimp\Campaign;

use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignRequest;

class CampaignRequestBuilder
{
    private $objectIdMapping;
    private $listId;
    private $fromName;
    private $segmentConditionsBuilder;

    public function __construct(
        MailchimpObjectIdMapping $objectIdMapping,
        SegmentConditionsBuilder $segmentConditionsBuilder,
        string $listId,
        string $fromName
    ) {
        $this->objectIdMapping = $objectIdMapping;
        $this->segmentConditionsBuilder = $segmentConditionsBuilder;
        $this->listId = $listId;
        $this->fromName = $fromName;
    }

    public function createEditCampaignRequestFromMessage(MailchimpCampaign $campaign): EditCampaignRequest
    {
        $message = $campaign->getMessage();

        return (new EditCampaignRequest($this->listId))
            ->setFolderId($this->objectIdMapping->getFolderIdByType($message->getType()))
            ->setTemplateId($this->objectIdMapping->getTemplateIdByType($message->getType()))
            ->setSubject($message->getSubject())
            ->setTitle($this->createCampaignLabel($campaign))
            ->setSegmentOptions($message->getFilter() ? $this->segmentConditionsBuilder->build($campaign) : [])
            ->setFromName($message->getFromName() ?? $this->fromName)
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
