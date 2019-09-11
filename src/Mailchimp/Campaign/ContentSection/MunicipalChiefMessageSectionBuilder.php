<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Utils\StringCleaner;

class MunicipalChiefMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function build(MailchimpCampaign $campaign, EditCampaignContentRequest $request): void
    {
        $message = $campaign->getMessage();
        $adherent = $message->getAuthor();

        $request
            ->addSection('first_name', StringCleaner::htmlspecialchars($adherent->getFirstName()))
            ->addSection('city_name', (string) $campaign->getLabel())
            ->addSection('reply_to_button', sprintf(
                '<a class="mcnButton" title="Répondre" href="mailto:%s" target="_blank">Répondre</a>',
                $email = $message->getAuthor()->getEmailAddress()
            ))
            ->addSection('reply_to_link', sprintf(
                '<a title="Répondre" href="mailto:%s" target="_blank">Répondre</a>',
                $email
            ))
        ;
    }
}
