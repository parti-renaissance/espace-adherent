<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\ContentSection;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use App\Scope\ScopeEnum;
use App\Utils\StringCleaner;

class DeputyMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return ScopeEnum::DEPUTY === $message->getInstanceScope();
    }

    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $request
            ->addSection('full_name', StringCleaner::htmlspecialchars((string) $message->getSender()?->getFullName()))
            ->addSection('first_name', StringCleaner::htmlspecialchars((string) $message->getSender()?->getFirstName()))
            ->addSection('district_name', (string) $message->senderZone)
            ->addSection('reply_to_button', $message->senderEmail ? \sprintf(
                '<a class="mcnButton" title="Répondre" href="mailto:%s" target="_blank">Répondre</a>',
                $message->senderEmail
            ) : '')
            ->addSection('reply_to_link', $message->senderEmail ? \sprintf(
                '<a title="Répondre" href="mailto:%s" target="_blank">Répondre</a>',
                $message->senderEmail
            ) : '')
        ;
    }
}
