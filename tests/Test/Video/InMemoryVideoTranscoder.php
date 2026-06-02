<?php

declare(strict_types=1);

namespace Tests\App\Test\Video;

use App\Entity\VideoStatusEnum;
use App\Video\Transcoding\TranscodingJobStatus;
use App\Video\Transcoding\VideoTranscoderInterface;

/**
 * Dev/test transcoder: no GCP call. Jobs are reported READY immediately so the pipeline can be
 * exercised locally and the synchronous test routing does not recurse on the polling message.
 */
class InMemoryVideoTranscoder implements VideoTranscoderInterface
{
    public function createJob(string $inputUri, string $outputUri, string $videoUuid): string
    {
        return 'fake-job/'.$videoUuid;
    }

    public function getJob(string $jobName): TranscodingJobStatus
    {
        return new TranscodingJobStatus(VideoStatusEnum::READY, width: 720, height: 720);
    }
}
