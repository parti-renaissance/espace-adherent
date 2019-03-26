<?php

namespace AppBundle\Mailchimp\Campaign;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignRequest;
use AppBundle\Utils\StringCleaner;

class CampaignRequestBuilder
{
    private $objectIdMapping;
    private $listId;
    private $replyEmailAddress;
    private $fromName;
    private $segmentConditionsBuilder;

    public function __construct(
        MailchimpObjectIdMapping $objectIdMapping,
        SegmentConditionsBuilder $segmentConditionsBuilder,
        string $listId,
        string $replyEmailAddress,
        string $fromName
    ) {
        $this->objectIdMapping = $objectIdMapping;
        $this->segmentConditionsBuilder = $segmentConditionsBuilder;
        $this->listId = $listId;
        $this->replyEmailAddress = $replyEmailAddress;
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
            ->setReplyTo($message->getReplyTo() ?? $this->replyEmailAddress)
        ;
    }

    public function createContentRequest(AdherentMessageInterface $message): EditCampaignContentRequest
    {
        $request = new EditCampaignContentRequest(
            $this->objectIdMapping->getTemplateIdByType($message->getType()),
            $message->getContent()
        );

        switch ($message->getType()) {
            case AdherentMessageTypeEnum::DEPUTY:
                $request
                    ->addSection('full_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFullName()))
                    ->addSection('first_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFirstName()))
                    ->addSection('district_name', (string) $message->getAuthor()->getManagedDistrict())
                ;
                break;

            case AdherentMessageTypeEnum::REFERENT:
                $request->addSection('first_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFirstName()));
                break;
        }

        return $request;
    }
}
