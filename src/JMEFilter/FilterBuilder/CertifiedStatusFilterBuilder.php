<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\Scope\ScopeEnum;

class CertifiedStatusFilterBuilder implements FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool
    {
        return ScopeEnum::NATIONAL === $scope;
    }

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        return new FilterCollectionBuilder()
            ->createBooleanSelect('isCertified', 'Certifié')
            ->getFilters()
        ;
    }
}
