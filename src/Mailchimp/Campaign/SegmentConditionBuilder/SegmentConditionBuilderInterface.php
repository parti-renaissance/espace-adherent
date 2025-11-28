<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;

interface SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool;

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array;

    public function buildFromFilter(SegmentFilterInterface $filter): array;
}
