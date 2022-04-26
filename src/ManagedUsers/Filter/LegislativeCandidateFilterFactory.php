<?php

namespace App\ManagedUsers\Filter;

use App\ManagedUsers\ManagedUsersFilter;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;

class LegislativeCandidateFilterFactory extends AbstractFilterFactory
{
    public function support(string $scopeCode): bool
    {
        return ScopeEnum::LEGISLATIVE_CANDIDATE === $scopeCode;
    }

    protected function getSubscriptionType(): string
    {
        return SubscriptionTypeEnum::CANDIDATE_EMAIL;
    }

    protected function updateFilter(ManagedUsersFilter $filter): void
    {
        $filter->withJeMengageUsers();
    }
}
