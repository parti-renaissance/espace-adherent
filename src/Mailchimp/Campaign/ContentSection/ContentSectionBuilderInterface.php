<?php

namespace App\Mailchimp\Campaign\ContentSection;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;

interface ContentSectionBuilderInterface
{
    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void;
}
