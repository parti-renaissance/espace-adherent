<?php

declare(strict_types=1);

namespace App\JMEFilter\Layout;

interface FilterLayoutInterface
{
    public function supports(string $scope, ?string $feature, bool $isVox): bool;

    public function getPriority(): int;

    /**
     * @return FilterGroupConfig[]
     */
    public function getGroupConfigs(string $scope): array;
}
