<?php

declare(strict_types=1);

namespace App\Api\Provider\Hub;

class HubItemSorter
{
    public function compare(HubItemRow $a, HubItemRow $b, bool $hasUserCoords): int
    {
        if ($hasUserCoords) {
            $aDistance = $a->distance ?? \PHP_FLOAT_MAX;
            $bDistance = $b->distance ?? \PHP_FLOAT_MAX;

            if ($aDistance !== $bDistance) {
                return $aDistance <=> $bDistance;
            }

            $beginAtCmp = $a->beginAt <=> $b->beginAt;

            if (0 !== $beginAtCmp) {
                return $beginAtCmp;
            }

            return $this->compareStable($a, $b);
        }

        if ($a->priority !== $b->priority) {
            return $b->priority <=> $a->priority;
        }

        if ($a->timeToBegin !== $b->timeToBegin) {
            return $a->timeToBegin <=> $b->timeToBegin;
        }

        return $this->compareStable($a, $b);
    }

    private function compareStable(HubItemRow $a, HubItemRow $b): int
    {
        return $a->entity->getUuid()->toRfc4122() <=> $b->entity->getUuid()->toRfc4122();
    }
}
