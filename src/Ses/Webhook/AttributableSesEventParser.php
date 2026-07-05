<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

interface AttributableSesEventParser
{
    /**
     * @param array<string, mixed> $snsPayload
     */
    public function parse(array $snsPayload): ?AttributableSesEvent;
}
