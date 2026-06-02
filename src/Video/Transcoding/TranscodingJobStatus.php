<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

use App\Entity\VideoStatusEnum;

/**
 * Snapshot of a transcoding job state, mapped onto the domain VideoStatusEnum.
 */
class TranscodingJobStatus
{
    public function __construct(
        public readonly VideoStatusEnum $state,
        public readonly ?string $error = null,
    ) {
    }
}
