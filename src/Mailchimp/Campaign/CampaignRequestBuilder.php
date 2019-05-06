<?php

namespace AppBundle\Mailchimp\Campaign;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
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

    public function createEditCampaignRequestFromMessage(AdherentMessageInterface $message): EditCampaignRequest
    {
        return (new EditCampaignRequest($this->listId))
            ->setFolderId($this->objectIdMapping->getFolderIdByType($message->getType()))
            ->setTemplateId($this->objectIdMapping->getTemplateIdByType($message->getType()))
            ->setSubject($message->getSubject())
            ->setTitle(sprintf('%s - %s', $message->getAuthor(), (new \DateTime())->format('d/m/Y')))
            ->setSegmentOptions($message->getFilter() ? $this->segmentConditionsBuilder->build($message) : [])
            ->setFromName($message->getFromName() ?? $this->fromName)
            ->setReplyTo($message->getReplyTo())
        ;
    }
}
