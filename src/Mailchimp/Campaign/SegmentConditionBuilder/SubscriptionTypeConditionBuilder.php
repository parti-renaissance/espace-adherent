<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\SegmentConditionBuilder;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\SegmentFilterInterface;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;

class SubscriptionTypeConditionBuilder extends AbstractConditionBuilder
{
    public function support(SegmentFilterInterface $filter): bool
    {
        return $filter instanceof AdherentMessageFilter
            || \in_array($filter->getMessage()?->getInstanceScope(), [
                ScopeEnum::DEPUTY,
                ScopeEnum::ANIMATOR,
                ScopeEnum::SENATOR,
                ScopeEnum::LEGISLATIVE_CANDIDATE,
                ScopeEnum::CANDIDATE,
                ScopeEnum::CORRESPONDENT,
                ScopeEnum::REGIONAL_COORDINATOR,
            ], true);
    }

    /**
     * @param AdherentMessageFilter $filter
     */
    public function buildFromFilter(SegmentFilterInterface $filter): array
    {
        if (!$subscriptionType = SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scope = $filter->getScope()] ?? null) {
            throw new \InvalidArgumentException(\sprintf('Scope %s does not match any subscription type', $scope));
        }

        return [$this->buildInterestCondition(
            [$subscriptionType],
            $this->mailchimpObjectIdMapping->getSubscriptionTypeInterestGroupId()
        )];
    }
}
