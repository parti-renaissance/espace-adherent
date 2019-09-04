<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;

interface ContentSectionBuilderInterface
{
    public function build(MailchimpCampaign $campaign, EditCampaignContentRequest $request): void;
}
