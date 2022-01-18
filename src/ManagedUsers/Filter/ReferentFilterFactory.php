<?php

namespace App\ManagedUsers\Filter;

use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;

class ReferentFilterFactory extends AbstractFilterFactory
{
    public function support(string $scopeCode): bool
    {
        return ScopeEnum::REFERENT === $scopeCode || ScopeEnum::CORRESPONDENT === $scopeCode;
    }

    protected function getSubscriptionType(): string
    {
        return SubscriptionTypeEnum::REFERENT_EMAIL;
    }
}
