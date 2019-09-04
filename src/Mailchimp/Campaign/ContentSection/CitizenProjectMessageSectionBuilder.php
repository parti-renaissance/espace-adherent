<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\Filter\CitizenProjectFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Utils\StringCleaner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenProjectMessageSectionBuilder implements ContentSectionBuilderInterface
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

        if (!$filter instanceof CitizenProjectFilter || !($citizenProject = $filter->getCitizenProject())) {
            return;
        }

        $request
            ->addSection('citizen_project_link', sprintf(
                '<a target="_blank" href="%s" title="Voir le projet citoyen">%s</a>',
                $this->urlGenerator->generate(
                    'app_citizen_project_show',
                    ['slug' => $citizenProject->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                StringCleaner::htmlspecialchars($citizenProject->getName())
            ))
            ->addSection('reply_to_link', sprintf(
                '<a class="mcnButton" title="Répondre" href="mailto:%s" target="_blank">Répondre</a>',
                $message->getAuthor()->getEmailAddress()
            ))
        ;
    }
}
