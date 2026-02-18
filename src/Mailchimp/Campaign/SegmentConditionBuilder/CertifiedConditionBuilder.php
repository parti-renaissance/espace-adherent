<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class CertifiedConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter && null !== $filter->getIsCertified();
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
            'op' => 'is',
            'field' => MemberRequest::MERGE_FIELD_CERTIFIED,
            'value' => $filter->getIsCertified() ? 'oui' : 'non',
        ]];
    }
}
