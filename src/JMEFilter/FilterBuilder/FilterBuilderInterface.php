<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterBuilder;

interface FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool;

    public function build(string $scope, ?string $feature = null, bool $isVox = false): array;
}
