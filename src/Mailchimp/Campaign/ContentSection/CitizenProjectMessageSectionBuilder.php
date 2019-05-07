<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\Filter\CitizenProjectFilter;
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

    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $filter = $message->getFilter();

        if (!$filter instanceof CitizenProjectFilter || !($citizenProject = $filter->getCitizenProject())) {
            return;
        }

        $request
            ->addSection('citizen_project_link', sprintf(
                '<a target="_blank" href="%s" title="Voir le projet citoyen" style="text-align:center;font-size:16px;color:#000;font-family:roboto,helvetica neue,helvetica,arial,sans-serif">%s</a>',
                $this->urlGenerator->generate(
                    'app_citizen_project_show',
                    ['slug' => $citizenProject->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                StringCleaner::htmlspecialchars($citizenProject->getName())
            ))
            ->addSection('reply_to_link', sprintf(
                '<a class="mcnButton" title="RÉPONDRE" href="mailto:%s" target="_blank" style="font-weight:normal;letter-spacing:normal;line-height:100%%;text-align:center;text-decoration:none;color:#FFDA00;mso-line-height-rule:exactly;-ms-text-size-adjust:100%%;-webkit-text-size-adjust:100%%;display:block;">RÉPONDRE</a>',
                $message->getAuthor()->getEmailAddress()
            ))
        ;
    }
}
