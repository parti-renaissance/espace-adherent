<?php

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Mailchimp\Campaign\MailchimpObjectIdMapping;

abstract class AbstractConditionBuilder implements SegmentConditionBuilderInterface
{
    protected const OP_INTEREST_ONE = 'interestcontains';
    protected const OP_INTEREST_ALL = 'interestcontainsall';
    protected const OP_INTEREST_NONE = 'interestnotcontains';

    protected $mailchimpObjectIdMapping;

    public function __construct(MailchimpObjectIdMapping $mailchimpObjectIdMapping)
    {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    protected function buildInterestCondition(
        array $interestKeys,
        string $groupId,
        string $op = self::OP_INTEREST_ALL
    ): array {
        return [
            'condition_type' => 'Interests',
            'op' => $op,
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
