<?php

namespace App\ManagedUsers\Filter;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Subscription\SubscriptionTypeEnum;

class CandidateFilterFactory extends AbstractFilterFactory
{
    public function support(string $spaceCode): bool
    {
        return \in_array($spaceCode, [AdherentSpaceEnum::CANDIDATE, AdherentSpaceEnum::LEGISLATIVE_CANDIDATE], true);
    }

    protected function getSubscriptionType(): string
    {
        return SubscriptionTypeEnum::CANDIDATE_EMAIL;
    }
}
