<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\MailchimpSegment;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class ReferentToElectedRepresentativeConditionBuilder extends AbstractConditionBuilder
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return $filter instanceof ReferentElectedRepresentativeFilter;
    }

    public function build(MailchimpCampaign $campaign): array
    {
        $conditions = array_map(function (MailchimpSegment $mailchimpSegment) {
            return $this->buildStaticSegmentCondition($mailchimpSegment->getExternalId());
        }, $campaign->getMailchimpSegments()->toArray());

        // Referents can send emails to adherents only
        $conditions[] = [
            'condition_type' => 'TextMerge',
            'op' => 'is',
            'field' => MemberRequest::MERGE_FIELD_ADHERENT,
            'value' => 'oui',
        ];

        return $conditions;
    }
}
