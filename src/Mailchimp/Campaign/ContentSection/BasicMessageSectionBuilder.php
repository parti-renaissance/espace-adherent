<?php

namespace App\Mailchimp\Campaign\ContentSection;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Entity\AdherentMessage\LreManagerElectedRepresentativeMessage;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use App\Entity\AdherentMessage\ReferentElectedRepresentativeMessage;
use App\Entity\AdherentMessage\ReferentInstancesMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use App\Utils\StringCleaner;

class BasicMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return \in_array(
            \get_class($message),
            [
                ReferentElectedRepresentativeMessage::class,
                LreManagerElectedRepresentativeMessage::class,
                LegislativeCandidateAdherentMessage::class,
                ReferentAdherentMessage::class,
                ReferentInstancesMessage::class,
                SenatorAdherentMessage::class,
            ],
            true
        );
    }

    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $request
            ->addSection('full_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFullName()))
            ->addSection('first_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFirstName()))
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
