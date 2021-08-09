<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\AbstractAdherentFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;

interface SegmentConditionBuilderInterface
{
    public function support(AdherentMessageFilterInterface $filter): bool;

    public function supportSegmentFilter(SegmentFilterInterface $filter): bool;

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array;

    public function buildFromFilter(AbstractAdherentFilter $filter): array;
}
