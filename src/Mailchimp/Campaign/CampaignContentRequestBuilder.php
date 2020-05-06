<?php

namespace App\Mailchimp\Campaign;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\ContentSection\ContentSectionBuilderInterface;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use Psr\Container\ContainerInterface;

class CampaignContentRequestBuilder
{
    private $objectIdMapping;
    private $sectionBuildersLocator;

    public function __construct(MailchimpObjectIdMapping $objectIdMapping, ContainerInterface $sectionBuildersLocator)
    {
        $this->objectIdMapping = $objectIdMapping;
        $this->sectionBuildersLocator = $sectionBuildersLocator;
    }

    public function createContentRequest(AdherentMessageInterface $message): EditCampaignContentRequest
    {
        $request = new EditCampaignContentRequest(
            $this->objectIdMapping->getTemplateIdByType($message->getType()),
            $message->getContent()
        );

        if ($sectionBuilder = $this->getSectionBuilder($message)) {
            $sectionBuilder->build($message, $request);
        }

        return $request;
    }

    private function getSectionBuilder(AdherentMessageInterface $message): ?ContentSectionBuilderInterface
    {
        if ($this->sectionBuildersLocator->has($key = \get_class($message))) {
            return $this->sectionBuildersLocator->get($key);
        }

        return null;
    }
}
