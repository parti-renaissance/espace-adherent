<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Mailchimp\Campaign\DateUtils;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class AdherentRegistrationDateConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter;
    }

    /**
     * @param AdherentMessageFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $registeredSince = $filter->getRegisteredSince() ?? new \DateTime('2016-04-06');

        $conditions = [[
            'condition_type' => 'DateMerge',
            'op' => 'greater',
            'field' => MemberRequest::MERGE_FIELD_ADHESION_DATE,
            'value' => DateUtils::adjustDate($registeredSince, false)->format(MemberRequest::DATE_FORMAT),
        ]];

        if ($registeredUntil = $filter->getRegisteredUntil()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => MemberRequest::MERGE_FIELD_ADHESION_DATE,
                'value' => DateUtils::adjustDate($registeredUntil, true)->format(MemberRequest::DATE_FORMAT),
            ];
        }

        return $conditions;
    }
}
