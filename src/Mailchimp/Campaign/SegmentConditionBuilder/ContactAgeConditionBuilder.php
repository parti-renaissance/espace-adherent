<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Mailchimp\Synchronisation\Request\MemberRequest;

class ContactAgeConditionBuilder implements SegmentConditionBuilderInterface
{
    public function support(AdherentMessageFilterInterface $filter): bool
    {
        return \in_array(\get_class($filter), [
            ReferentUserFilter::class,
            CommitteeFilter::class,
        ], true);
    }

    public function build(MailchimpCampaign $campaign): array
    {
        /** @var CommitteeFilter|ReferentUserFilter $filter */
        $filter = $campaign->getMessage()->getFilter();

        $conditions = [];

        $now = new \DateTimeImmutable('now');

        if ($minAge = $filter->getAgeMin()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'less',
                'field' => MemberRequest::MERGE_FIELD_BIRTHDATE,
                'value' => $now->modify(sprintf('-%d years', $minAge))->format(MemberRequest::DATE_FORMAT),
            ];
        }

        if ($maxAge = $filter->getAgeMax()) {
            $conditions[] = [
                'condition_type' => 'DateMerge',
                'op' => 'greater',
                'field' => MemberRequest::MERGE_FIELD_BIRTHDATE,
                'value' => $now->modify(sprintf('-%d years', $maxAge))->format(MemberRequest::DATE_FORMAT),
            ];
        }

        return $conditions;
    }
}
