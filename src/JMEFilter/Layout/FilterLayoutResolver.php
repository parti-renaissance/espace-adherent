<?php

declare(strict_types=1);

namespace App\JMEFilter\Layout;

class FilterLayoutResolver
{
    public function __construct(private readonly iterable $layouts)
    {
    }

    public function resolve(string $scope, ?string $feature, bool $isVox): FilterLayoutInterface
    {
        $candidates = [];

        foreach ($this->layouts as $layout) {
            if ($layout->supports($scope, $feature, $isVox)) {
                $candidates[] = $layout;
            }
        }

        if (0 === \count($candidates)) {
            throw new \RuntimeException(\sprintf('No layout found for scope "%s", feature "%s", isVox "%s"', $scope, $feature ?? 'null', $isVox ? 'true' : 'false'));
        }

        usort($candidates, static function (FilterLayoutInterface $a, FilterLayoutInterface $b): int {
            return $b->getPriority() <=> $a->getPriority();
        });

        return $candidates[0];
    }
}
