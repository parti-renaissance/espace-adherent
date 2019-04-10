<?php

namespace AppBundle\Mailchimp\Campaign\ContentSection;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Mailchimp\Campaign\Request\EditCampaignContentRequest;

interface ContentSectionBuilderInterface
{
    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void;
}
