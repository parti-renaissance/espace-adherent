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

    public function __construct(MailchimpObjectIdMapping $objectIdMapping)
    {
        $this->objectIdMapping = $objectIdMapping;
    }

    public function createEditCampaignRequestFromMessage(AdherentMessageInterface $message): EditCampaignRequest
    {
        return (new EditCampaignRequest())
            ->setFolderId($this->objectIdMapping->getFolderIdForType($message->getType()))
            ->setTemplateId($this->objectIdMapping->getTemplateIdForType($message->getType()))
            ->setSubject($message->getSubject())
            ->setTitle(sprintf('%s - %s', $message->getAuthor(), (new \DateTime())->format('d/m/Y')))
        ;
    }

    public function createContentRequest(AdherentMessageInterface $message): EditCampaignContentRequest
    {
        $request = new EditCampaignContentRequest(
            $this->objectIdMapping->getTemplateIdForType($message->getType()),
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
