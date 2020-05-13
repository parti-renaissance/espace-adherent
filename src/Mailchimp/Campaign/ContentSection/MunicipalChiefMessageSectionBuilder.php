<?php

namespace App\Mailchimp\Campaign\ContentSection;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use App\Utils\StringCleaner;

class MunicipalChiefMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $adherent = $message->getAuthor();

        $request
            ->addSection('first_name', StringCleaner::htmlspecialchars($adherent->getFirstName()))
            ->addSection('city_name', $adherent->getMunicipalChiefManagedArea()->getCityName())
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
