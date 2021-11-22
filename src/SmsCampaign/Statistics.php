<?php

namespace App\SmsCampaign;

class Statistics
{
    public int $credits = 0;
    public int $delivered = 0;
    public int $estimatedCredits = 0;
    public int $failed = 0;
    public int $pending = 0;
    public int $sent = 0;
    public int $stoplisted = 0;

    public static function createFromResponse(array $response): self
    {
        $instance = new self();

        $instance->credits = $response['credits'] ?? 0;
        $instance->delivered = $response['delivered'] ?? 0;
        $instance->estimatedCredits = $response['estimatedCredits'] ?? 0;
        $instance->failed = $response['failed'] ?? 0;
        $instance->pending = $response['pending'] ?? 0;
        $instance->sent = $response['sent'] ?? 0;
        $instance->stoplisted = $response['stoplisted'] ?? 0;

        return $instance;
    }
}
