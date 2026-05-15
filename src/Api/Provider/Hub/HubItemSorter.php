<?php

declare(strict_types=1);

namespace App\Api\Provider\Hub;

class HubItemSorter
{
    public const string ORDER_BEGIN_AT = 'beginAt';
    public const string ORDER_CREATED_AT = 'createdAt';
    public const string ORDER_FINISH_AT = 'finishAt';
    public const string ORDER_SUBSCRIPTIONS = 'subscriptions';

    public const array SUPPORTED_ORDERS = [
        self::ORDER_BEGIN_AT,
        self::ORDER_CREATED_AT,
        self::ORDER_FINISH_AT,
        self::ORDER_SUBSCRIPTIONS,
    ];

    public function compare(
        HubItemRow $a,
        HubItemRow $b,
        bool $hasUserCoords,
        ?string $orderKey = null,
        string $direction = 'asc',
    ): int {
        if (null !== $orderKey && \in_array($orderKey, self::SUPPORTED_ORDERS, true)) {
            $cmp = $this->compareByKey($a, $b, $orderKey);

            if (0 !== $cmp) {
                return 'desc' === $direction ? -$cmp : $cmp;
            }

            return $this->compareStable($a, $b);
        }

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

    private function compareByKey(HubItemRow $a, HubItemRow $b, string $key): int
    {
        return match ($key) {
            self::ORDER_BEGIN_AT => $a->beginAt <=> $b->beginAt,
            self::ORDER_CREATED_AT => $a->createdAt <=> $b->createdAt,
            // null finishAt (Action) goes last in ASC, first in DESC.
            self::ORDER_FINISH_AT => $this->compareNullable($a->finishAt, $b->finishAt),
            self::ORDER_SUBSCRIPTIONS => $a->participantsCount <=> $b->participantsCount,
            default => 0,
        };
    }

    private function compareNullable(?\DateTimeInterface $a, ?\DateTimeInterface $b): int
    {
        if (null === $a && null === $b) {
            return 0;
        }

        if (null === $a) {
            return 1;
        }

        if (null === $b) {
            return -1;
        }

        return $a <=> $b;
    }

    private function compareStable(HubItemRow $a, HubItemRow $b): int
    {
        return $a->entity->getUuid()->toRfc4122() <=> $b->entity->getUuid()->toRfc4122();
    }
}
