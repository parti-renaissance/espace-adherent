<?php

namespace App\ManagedUsers\Filter;

use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;

class DeputyFilterFactory extends AbstractFilterFactory
{
    public function support(string $scopeCode): bool
    {
        return ScopeEnum::DEPUTY === $scopeCode;
    }

    protected function getSubscriptionType(): string
    {
        return SubscriptionTypeEnum::DEPUTY_EMAIL;
    }
}
