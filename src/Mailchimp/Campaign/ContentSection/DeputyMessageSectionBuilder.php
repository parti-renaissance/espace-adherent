<?php

namespace App\Mailchimp\Campaign\ContentSection;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use App\Utils\StringCleaner;

class DeputyMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof DeputyAdherentMessage;
    }

    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $request
            ->addSection('full_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFullName()))
            ->addSection('first_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFirstName()))
            ->addSection('district_name', (string) $message->getAuthor()->getManagedDistrict())
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
