<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

/**
 * Media dimensions and duration probed from a transcoded video's HLS output. Any field may be null
 * when it could not be determined (probe failure, missing RESOLUTION, etc.).
 */
class VideoMediaInfo
{
    public function __construct(
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly ?int $duration = null,
    ) {
    }
}
