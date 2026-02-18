<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\SegmentFilterInterface;

interface SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool;

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array;

    public function buildFromFilter(SegmentFilterInterface $filter): array;
}
