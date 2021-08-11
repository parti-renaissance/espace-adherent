<?php

namespace App\ManagedUsers\Filter;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Subscription\SubscriptionTypeEnum;

class SenatorFilterFactory extends AbstractFilterFactory
{
    public function support(string $spaceCode): bool
    {
        return AdherentSpaceEnum::SENATOR === $spaceCode;
    }

    protected function getSubscriptionType(): string
    {
        return SubscriptionTypeEnum::SENATOR_EMAIL;
    }
}
