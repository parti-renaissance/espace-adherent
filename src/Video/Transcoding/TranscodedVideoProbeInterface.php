<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

use App\Entity\Video;

interface TranscodedVideoProbeInterface
{
    /**
     * Probes the transcoded HLS output of a READY video for its dimensions and duration.
     * Best-effort: returns an empty VideoMediaInfo on any failure (never throws).
     */
    public function probe(Video $video): VideoMediaInfo;
}
