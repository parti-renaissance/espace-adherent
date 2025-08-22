<?php

namespace App\JMEFilter\FilterBuilder;

interface FilterBuilderInterface
{
    public function supports(string $scope, ?string $feature = null): bool;

    public function build(string $scope, ?string $feature = null): array;

    public function getGroup(string $scope, ?string $feature = null): string;
}
