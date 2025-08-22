<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\Mailchimp\Campaign\AudienceTypeEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;

class LegislativeCandidateFieldsFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        // temporarily disable this filter, need setup conditionBuilder to filter in global NL audience
        return false && ScopeEnum::LEGISLATIVE_CANDIDATE === $scope && \in_array($feature, [FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS]);
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('audienceType', 'Audience')
            ->setRequired(true)
            ->withEmptyChoice(FeatureEnum::PUBLICATIONS === $feature)
            ->setChoices([
                AudienceTypeEnum::ADHERENT => 'Adhérents',
                AudienceTypeEnum::LEGISLATIVE_CANDIDATE_NEWSLETTER => 'Inscrit newsletter',
            ])
            ->getFilters()
        ;
    }

    public function getGroup(string $scope, ?string $feature = null): string
    {
        return MilitantFilterGroup::class;
    }
}
