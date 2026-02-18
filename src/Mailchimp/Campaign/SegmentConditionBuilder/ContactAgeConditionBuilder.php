<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Mailchimp\Campaign\DateUtils;
use App\Mailchimp\Synchronisation\Request\MemberRequest;

class ContactAgeConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter;
    }

    public function buildFromMailchimpCampaign(MailchimpCampaign $campaign): array
    {
        if (null !== $campaign->getMailchimpListType()) {
            return [];
        }

        return $this->buildFromFilter($campaign->getMessage()->getFilter());
    }

    /**
     * @param AdherentMessageFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        $conditions = [];

        $now = new \DateTimeImmutable('now');

        if ($minAge = $filter->getAgeMin()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => MemberRequest::MERGE_FIELD_BIRTHDATE,
                'value' => DateUtils::adjustDate($now, true)->modify(\sprintf('-%d years', $minAge))->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($maxAge = $filter->getAgeMax()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => MemberRequest::MERGE_FIELD_BIRTHDATE,
                'value' => DateUtils::adjustDate($now, false)->modify(\sprintf('-%d years', $maxAge))->format(MemberRequest::DATE_FORMAT),
            ];
        }

        return $conditions;
    }
}
