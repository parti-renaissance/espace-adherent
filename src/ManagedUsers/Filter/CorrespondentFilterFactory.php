<?php

namespace App\ManagedUsers\Filter;

use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;

class CorrespondentFilterFactory extends AbstractFilterFactory
{
    public function support(string $scopeCode): bool
    {
        return ScopeEnum::CORRESPONDENT === $scopeCode;
    }

    protected function getSubscriptionType(): string
    {
        return SubscriptionTypeEnum::REFERENT_EMAIL;
    }
}
