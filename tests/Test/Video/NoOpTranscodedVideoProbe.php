<?php

declare(strict_types=1);

namespace Tests\App\Test\Video;

use App\Entity\Video;
use App\Video\Transcoding\TranscodedVideoProbeInterface;
use App\Video\Transcoding\VideoMediaInfo;

/**
 * Dev/test probe: does not perform any HTTP request, returns no media info.
 */
class NoOpTranscodedVideoProbe implements TranscodedVideoProbeInterface
{
    public function probe(Video $video): VideoMediaInfo
    {
        return new VideoMediaInfo();
    }
}
