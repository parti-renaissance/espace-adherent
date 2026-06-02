<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

use App\Entity\VideoStatusEnum;
use Google\Cloud\Video\Transcoder\V1\Client\TranscoderServiceClient;
use Google\Cloud\Video\Transcoder\V1\CreateJobRequest;
use Google\Cloud\Video\Transcoder\V1\GetJobRequest;
use Google\Cloud\Video\Transcoder\V1\Job;
use Google\Cloud\Video\Transcoder\V1\Job\ProcessingState;

class GcpVideoTranscoder implements VideoTranscoderInterface
{
    private const int TTL_AFTER_COMPLETION_DAYS = 7;

    public function __construct(
        private readonly TranscoderServiceClient $client,
        private readonly TranscodingJobConfigFactory $configFactory,
        private readonly string $transcoderProjectId,
        private readonly string $transcoderLocation,
    ) {
    }

    public function createJob(string $inputUri, string $outputUri, string $videoUuid): string
    {
        $job = new Job([
            'input_uri' => $inputUri,
            'output_uri' => $outputUri,
            'config' => $this->configFactory->create(),
            'ttl_after_completion_days' => self::TTL_AFTER_COMPLETION_DAYS,
            'labels' => ['video_uuid' => $videoUuid],
        ]);

        $parent = $this->client::locationName($this->transcoderProjectId, $this->transcoderLocation);

        // The Transcoder API does not accept a client-supplied job id (CreateJobRequest exposes only
        // parent + job), so creation is not idempotent at the API level. Idempotency is enforced
        // upstream by the per-message lock and the caller's status guard (launch happens once per Video).
        return $this->client->createJob(CreateJobRequest::build($parent, $job))->getName();
    }

    public function getJob(string $jobName): TranscodingJobStatus
    {
        $job = $this->client->getJob(new GetJobRequest(['name' => $jobName]));

        $state = match ($job->getState()) {
            ProcessingState::SUCCEEDED => VideoStatusEnum::READY,
            ProcessingState::FAILED => VideoStatusEnum::FAILED,
            default => VideoStatusEnum::PROCESSING,
        };

        return new TranscodingJobStatus(
            $state,
            VideoStatusEnum::FAILED === $state ? $job->getError()?->getMessage() : null,
        );
    }
}
