<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Mailchimp\Campaign\MailchimpObjectIdMapping;

abstract class AbstractConditionBuilder implements SegmentConditionBuilderInterface
{
    protected const OP_INTEREST_ONE = 'interestcontains';
    protected const OP_INTEREST_ALL = 'interestcontainsall';
    protected const OP_INTEREST_NONE = 'interestnotcontains';

    public function __construct(protected readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping)
    {
    }

    protected function buildInterestCondition(
        array $interestKeys,
        string $groupId,
        string $op = self::OP_INTEREST_ALL,
    ): array {
        return [
            'condition_type' => 'Interests',
            'op' => $op,
            'field' => \sprintf('interests-%s', $groupId),
            'value' => array_values(
                array_intersect_key($this->getListInterestIds(), array_fill_keys($interestKeys, true))
            ),
        ];
    }

    protected function getListInterestIds(): array
    {
        return $this->mailchimpObjectIdMapping->getInterestIds();
    }
}
