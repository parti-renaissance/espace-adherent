<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

interface VideoTranscoderInterface
{
    /**
     * Creates a transcoding job and returns its resource name. $withAudio is false when the source has
     * no audio track (reactive retry), producing a video-only output.
     */
    public function createJob(string $inputUri, string $outputUri, string $videoUuid, bool $withAudio = true): string;

    public function getJob(string $jobName): TranscodingJobStatus;
}
