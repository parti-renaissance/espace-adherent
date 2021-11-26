<?php

namespace App\SmsCampaign;

class Statistics
{
    private int $credits = 0;
    private int $delivered = 0;
    private int $estimatedCredits = 0;
    private int $failed = 0;
    private int $pending = 0;
    private int $sent = 0;
    private int $stoplisted = 0;

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

    public function getContacts(): int
    {
        return $this->estimatedCredits;
    }

    public function getNonSent(): int
    {
        $value = $this->estimatedCredits - $this->sent - $this->pending;

        return $value >= 0 ? $value : 0;
    }

    public function getFailedSent(): int
    {
        return $this->credits - $this->delivered;
    }

    public function getPending(): int
    {
        return $this->pending;
    }

    public function getStoplisted(): int
    {
        return $this->stoplisted;
    }

    public function getSent(): int
    {
        return $this->credits;
    }

    public function getDelivered(): int
    {
        return $this->delivered;
    }
}
