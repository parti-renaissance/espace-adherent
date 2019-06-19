<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use AppBundle\Utils\StringCleaner;

class MunicipalChiefMessageSectionBuilder implements ContentSectionBuilderInterface
{
    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void
    {
        $request
            ->addSection('first_name', StringCleaner::htmlspecialchars($message->getAuthor()->getFirstName()))
            ->addSection('reply_to_link', sprintf(
                '<a class="mcnButton" title="RÉPONDRE" href="mailto:%s" target="_blank" style="font-weight:normal;letter-spacing:normal;line-height:100%%;text-align:center;text-decoration:none;color:#000;mso-line-height-rule:exactly;-ms-text-size-adjust:100%%;-webkit-text-size-adjust:100%%;display:block;">RÉPONDRE</a>',
                $message->getAuthor()->getEmailAddress()
            ))
        ;
    }
}
