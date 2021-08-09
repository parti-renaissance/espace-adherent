<?php

namespace App\ManagedUsers\Filter;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Subscription\SubscriptionTypeEnum;

class ReferentFilterFactory extends AbstractFilterFactory
{
    public function support(string $spaceCode): bool
    {
        return AdherentSpaceEnum::REFERENT === $spaceCode;
    }

    protected function getSubscriptionType(): string
    {
        return SubscriptionTypeEnum::REFERENT_EMAIL;
    }
}
