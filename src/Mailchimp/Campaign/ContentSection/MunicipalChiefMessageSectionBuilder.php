<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Utils\StringCleaner;

class MunicipalChiefMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $request
            ->addSection('first_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFirstName()))
            ->addSection('reply_to_link', sprintf(
                '<a class="mcnButton" title="Répondre" href="mailto:%s" target="_blank">Répondre</a>',
                $message->getAuthor()->getEmailAddress()
            ))
        ;
    }
}
