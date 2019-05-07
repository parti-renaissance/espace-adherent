<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
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

    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $filter = $message->getFilter();

        if (!$filter instanceof CommitteeFilter || !($committee = $filter->getCommittee())) {
            return;
        }

        $request
            ->addSection('committee_link', sprintf(
                '<a target="_blank" href="%s" title="Voir le comité" style="text-align:center;font-size:16px;color:#000;font-family:roboto,helvetica neue,helvetica,arial,sans-serif">%s</a>',
                $this->urlGenerator->generate(
                    'app_committee_show',
                    ['slug' => $committee->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                StringCleaner::htmlspecialchars($committee->getName())
            ))
            ->addSection('reply_to_link', sprintf(
                '<a class="mcnButton" title="RÉPONDRE" href="mailto:%s" target="_blank" style="font-weight:normal;letter-spacing:normal;line-height:100%%;text-align:center;text-decoration:none;color:#2BBAFF;">RÉPONDRE</a>',
                $message->getAuthor()->getEmailAddress()
            ))
        ;
    }
}
