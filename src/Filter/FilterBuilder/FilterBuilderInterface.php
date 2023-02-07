<?php

namespace App\Filter\FilterBuilder;

interface FilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool;

    public function build(string $scope, string $feature = null): array;
}
