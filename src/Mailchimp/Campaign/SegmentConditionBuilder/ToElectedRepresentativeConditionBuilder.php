<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\Filter\AbstractElectedRepresentativeFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\MailchimpSegment;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class ToElectedRepresentativeConditionBuilder extends AbstractConditionBuilder
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AbstractElectedRepresentativeFilter;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        $conditions = array_map(function (MailchimpSegment $mailchimpSegment) {
            return $this->buildStaticSegmentCondition($mailchimpSegment->getExternalId());
        }, $campaign->getMailchimpSegments());

        // Referents can send emails to adherents only
        $conditions[] = [
            'condition_type' => 'TextMerge',
            'op' => 'is',
            'field' => MemberRequest::MERGE_FIELD_ADHERENT,
            'value' => 'oui',
        ];

        return $conditions;
    }

    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        return [];
    }
}
