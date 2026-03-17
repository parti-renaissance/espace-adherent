<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;

class CertifiedStatusFilterBuilder implements FilterBuilderInterface
{
    public function build(string $scope, ?string $feature = null, bool $isVox = false): array
    {
        return new FilterCollectionBuilder()
            ->createBooleanSelect('isCertified', 'Certifié')
            ->getFilters()
        ;
    }
}
