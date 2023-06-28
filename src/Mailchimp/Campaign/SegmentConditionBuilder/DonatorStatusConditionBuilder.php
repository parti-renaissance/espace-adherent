<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Donation\DonatorStatusEnum;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\SegmentFilterInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class DonatorStatusConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AudienceFilter && null !== $filter->getDonatorStatus();
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param AudienceFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $value = match ($filterValue = $filter->getDonatorStatus()) {
            DonatorStatusEnum::DONATOR_N,
            DonatorStatusEnum::DONATOR_N_X => date('Y'),
            default => '',
        };

        $operation = match ($filterValue) {
            DonatorStatusEnum::DONATOR_N => 'contains',
            DonatorStatusEnum::DONATOR_N_X => 'notcontain',
            DonatorStatusEnum::NOT_DONATOR => 'blank',
        };

        $condition = [[
            'condition_type' => 'TextMerge',
            'op' => $operation,
            'field' => MemberRequest::MERGE_FIELD_DONATION_YEARS,
            'value' => $value,
        ]];

        if (DonatorStatusEnum::DONATOR_N_X === $filterValue) {
            $condition[] = [
                'condition_type' => 'TextMerge',
                'op' => 'blank_not',
                'field' => MemberRequest::MERGE_FIELD_DONATION_YEARS,
            ];
        }

        return $condition;
    }
}
