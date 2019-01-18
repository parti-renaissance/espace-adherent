<?php

namespace AppBundle\Mailchimp\Campaign;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\Filter\FilterDataObjectInterface;
use AppBundle\AdherentMessage\Filter\ReferentFilterDataObject;
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

    public function __construct(
        MailchimpObjectIdMapping $objectIdMapping,
        string $listId,
        string $replyEmailAddress,
        string $fromName
    ) {
        $this->objectIdMapping = $objectIdMapping;
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
            ->setConditions($this->buildFilterConditions($message->getFilter()))
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

    private function buildFilterConditions(?FilterDataObjectInterface $filter): array
    {
        if ($filter instanceof ReferentFilterDataObject) {
            return $this->buildReferentConditions($filter);
        }

        return [];
    }

    private function buildReferentConditions(ReferentFilterDataObject $filter): array
    {
        return array_map(function (string $zone) {
            return [
                'condition_type' => 'StaticSegment',
                'op' => 'static_is',
                'field' => 'static_segment',
                'value' => $zone,
            ];
        }, array_values($filter->getZones()));
    }
}
