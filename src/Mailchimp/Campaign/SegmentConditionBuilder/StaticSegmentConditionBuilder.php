<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\SegmentFilterInterface;

class StaticSegmentConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        if (null === $staticSegmentId = $campaign->getStaticSegmentId()) {
            return [];
        }

        return [
            [
                'condition_type' => 'StaticSegment',
                'field' => 'static_segment',
                'op' => 'static_is',
                'value' => $staticSegmentId,
            ],
        ];
    }

    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        return [];
    }
}
