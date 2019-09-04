<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Utils\StringCleaner;

class DeputyMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function build(MailchimpCampaign $campaign, EditCampaignContentRequest $request): void
    {
        $message = $campaign->getMessage();

        $request
            ->addSection('full_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFullName()))
            ->addSection('first_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFirstName()))
            ->addSection('district_name', (string) $message->getAuthor()->getManagedDistrict())
        ;
    }
}
