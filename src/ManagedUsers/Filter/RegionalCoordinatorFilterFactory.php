<?php

namespace App\ManagedUsers\Filter;

use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;

class RegionalCoordinatorFilterFactory extends AbstractFilterFactory
{
    public function support(string $scopeCode): bool
    {
        return ScopeEnum::REGIONAL_COORDINATOR === $scopeCode;
    }

    protected function getSubscriptionType(): string
    {
        return SubscriptionTypeEnum::REFERENT_EMAIL;
    }
}
