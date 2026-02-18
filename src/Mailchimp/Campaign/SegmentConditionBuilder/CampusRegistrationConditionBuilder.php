<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class CampusRegistrationConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter && null !== $filter->getIsCampusRegistered();
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param AdherentMessageFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        return [[
            'condition_type' => 'TextMerge',
            'op' => $filter->getIsCampusRegistered() ? 'blank_not' : 'blank',
            'field' => MemberRequest::MERGE_FIELD_CAMPUS_REGISTRATION_DATE,
        ]];
    }
}
