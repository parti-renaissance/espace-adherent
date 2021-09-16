<?php

namespace App\AdherentFilter\FilterBuilder;

interface AdherentFilterBuilderInterface
{
    public function supports(string $scope, string $feature = null): bool;

    public function build(string $scope, string $feature = null): array;
}
