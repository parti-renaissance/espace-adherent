<?php

namespace App\Mailchimp\Campaign\ContentSection;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use App\Utils\StringCleaner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitteeMessageSectionBuilder implements ContentSectionBuilderInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $filter = $message->getFilter();

        if (!$filter instanceof CommitteeFilter || !($committee = $filter->getCommittee())) {
            return;
        }

        $request
            ->addSection('committee_link', sprintf(
                '<a target="_blank" href="%s" title="Voir le comité">%s</a>',
                $this->urlGenerator->generate(
                    'app_committee_show',
                    ['slug' => $committee->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                StringCleaner::htmlspecialchars($committee->getName())
            ))
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
