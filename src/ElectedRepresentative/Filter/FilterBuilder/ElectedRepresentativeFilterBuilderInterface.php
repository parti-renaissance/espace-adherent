<?php

namespace App\ElectedRepresentative\Filter\FilterBuilder;

interface ElectedRepresentativeFilterBuilderInterface
{
    public function supports(string $scope): bool;

    public function build(string $scope): array;
}
