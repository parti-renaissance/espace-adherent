<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\ContentSection;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;

interface ContentSectionBuilderInterface
{
    public function supports(AdherentMessageInterface $message): bool;

    public function build(AdherentMessageInterface $message, EditCampaignContentRequest $request): void;
}
