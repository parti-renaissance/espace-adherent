<?php

namespace App\Mailchimp\Campaign\ContentSection;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CoalitionsMessage;
use App\Entity\AdherentMessage\Filter\CoalitionsFilter;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use App\Utils\StringCleaner;

class CoalitionMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof CoalitionsMessage;
    }

    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $filter = $message->getFilter();

        if (!$filter instanceof CoalitionsFilter || !($cause = $filter->getCause())) {
            return;
        }

        $author = $cause->getAuthor();

        $request
            ->addSection('coalition_name', StringCleaner::htmlspecialchars($cause->getCoalition()->getName()))
            ->addSection('cause_name', StringCleaner::htmlspecialchars($cause->getName()))
            ->addSection('author_full_name', StringCleaner::htmlspecialchars($fullName = $author->getFullName()))
            ->addSection('reply_to_link', sprintf(
                '<a title="Répondre" href="mailto:%s" target="_blank">Répondre</a>',
                $email = $message->getAuthor()->getEmailAddress()
            ))
            ->addSection('reply_to_button', sprintf(
                '<a class="mcnButton" title="Répondre" href="mailto:%s" target="_blank">Répondre à %s</a>',
                $email,
                $fullName
            ))
        ;
    }
}
