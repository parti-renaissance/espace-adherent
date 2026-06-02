<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

interface VideoTranscoderInterface
{
    /**
     * Creates a transcoding job and returns its resource name.
     */
    public function createJob(string $inputUri, string $outputUri, string $videoUuid): string;

    public function getJob(string $jobName): TranscodingJobStatus;
}
