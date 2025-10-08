<?php

namespace App\JeMengage\Hit\Stats\DTO;

class StatsOutput implements \JsonSerializable
{
    public function __construct(
        public readonly ?int $uniqueImpressions,
        public readonly ?int $uniqueImpressionsFromList,
        public readonly ?int $uniqueImpressionsFromTimeline,
        public readonly ?int $uniqueOpens,
        public readonly ?int $uniqueOpensFromTimeline,
        public readonly ?int $uniqueOpensFromNotification,
        public readonly ?int $uniqueOpensFromDirectLink,
        public readonly ?int $uniqueOpensFromList,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'unique_impressions' => [
                'total' => $this->uniqueImpressions,
                'timeline' => $this->uniqueImpressionsFromTimeline,
                'list' => $this->uniqueImpressionsFromList,
            ],
            'unique_opens' => [
                'total' => $this->uniqueOpens,
                'timeline' => $this->uniqueOpensFromTimeline,
                'notification' => $this->uniqueOpensFromNotification,
                'direct_link' => $this->uniqueOpensFromDirectLink,
                'list' => $this->uniqueOpensFromList,
            ],
        ];
    }
}
