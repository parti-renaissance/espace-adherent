<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;

interface SegmentConditionBuilderInterface
{
    public function support(AdherentMessageFilterInterface $filter): bool;

    public function build(MailchimpCampaign $campaign): array;
}
