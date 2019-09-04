<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Utils\StringCleaner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitteeMessageSectionBuilder implements ContentSectionBuilderInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function build(MailchimpCampaign $campaign, EditCampaignContentRequest $request): void
    {
        $message = $campaign->getMessage();
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
            ->addSection('reply_to_link', sprintf(
                '<a class="mcnButton" title="Répondre" href="mailto:%s" target="_blank">Répondre</a>',
                $message->getAuthor()->getEmailAddress()
            ))
        ;
    }
}
