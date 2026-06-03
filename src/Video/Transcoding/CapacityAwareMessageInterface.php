<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

/**
 * A transcoding command that can be re-dispatched when the transcoder is at capacity, carrying its own
 * deferral attempt counter so the count survives the transport round-trip. Lets the shared deferral
 * service rebuild the next message without knowing the concrete command type.
 */
interface CapacityAwareMessageInterface
{
    public function getCapacityAttempt(): int;

    public function withNextCapacityAttempt(): self;
}
