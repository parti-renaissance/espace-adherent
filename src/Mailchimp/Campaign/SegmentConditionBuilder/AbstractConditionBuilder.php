<?php

namespace AppBundle\Mailchimp\Campaign\SegmentConditionBuilder;

use AppBundle\Mailchimp\Campaign\MailchimpObjectIdMapping;

abstract class AbstractConditionBuilder implements SegmentConditionBuilderInterface
{
    protected $mailchimpObjectIdMapping;

    public function __construct(MailchimpObjectIdMapping $mailchimpObjectIdMapping)
    {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    protected function buildInterestCondition(array $interestKeys, string $groupId, bool $matchAll = true): array
    {
        return [
            'condition_type' => 'Interests',
            'op' => $matchAll ? 'interestcontainsall' : 'interestcontains',
            'field' => sprintf('interests-%s', $groupId),
            'value' => array_values(
                array_intersect_key($this->mailchimpObjectIdMapping->getInterestIds(), array_fill_keys($interestKeys, true))
            ),
        ];
    }

    protected function buildStaticSegmentCondition(int $externalId): array
    {
        return [
            'condition_type' => 'StaticSegment',
            'op' => 'static_is',
            'field' => 'static_segment',
            'value' => $externalId,
        ];
    }
}
