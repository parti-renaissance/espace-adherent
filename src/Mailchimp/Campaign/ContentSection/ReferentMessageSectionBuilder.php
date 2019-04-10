<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Utils\StringCleaner;

class ReferentMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $request->addSection('first_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFirstName()));
    }
}
