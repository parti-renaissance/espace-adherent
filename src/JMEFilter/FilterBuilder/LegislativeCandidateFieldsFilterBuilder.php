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
        return ScopeEnum::LEGISLATIVE_CANDIDATE === $scope && FeatureEnum::MESSAGES === $feature;
    }

    public function build(string $scope, ?string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('audienceType', 'Audience')
            ->setRequired(true)
            ->setChoices([
                AudienceTypeEnum::ADHERENT => 'Adhérents',
                AudienceTypeEnum::LEGISLATIVE_CANDIDATE_NEWSLETTER => 'Inscrit newsletter',
            ])
            ->getFilters()
        ;
    }

    public function getGroup(): string
    {
        return MilitantFilterGroup::class;
    }
}
