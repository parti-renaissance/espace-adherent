<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\ContentSection;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use App\Scope\ScopeEnum;
use App\Utils\StringCleaner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitteeMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return ScopeEnum::ANIMATOR === $message->getInstanceScope();
    }

    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $filter = $message->getFilter();

        if (!$committee = $filter?->getCommittee()) {
            return;
        }

        $request
            ->addSection('committee_link', \sprintf(
                '<a target="_blank" href="%s" title="Voir le comité">%s</a>',
                $this->urlGenerator->generate(
                    'app_committee_show',
                    ['slug' => $committee->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                StringCleaner::htmlspecialchars($committee->getName())
            ))
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
