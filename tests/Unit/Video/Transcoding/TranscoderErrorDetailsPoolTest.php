<?php

declare(strict_types=1);

namespace Tests\App\Unit\Video\Transcoding;

use Google\ApiCore\Serializer;
use Google\Cloud\Video\Transcoder\V1\Job;
use Google\Cloud\Video\Transcoder\V1\Job\ProcessingState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * Regression for the descriptor-pool crash: getJob() on a FAILED job whose error.details carry a
 * google.rpc.BadRequest used to throw while parsing the REST response, which eagerly unpacks the Any
 * for that type while it is unregistered. The exact message depends on the protobuf runtime: pure-PHP
 * reports "Class google.rpc.BadRequest hasn't been added to descriptor pool", the C extension reports
 * "Type was not found". GcpVideoTranscoder preloads the gax known metadata types to register them;
 * these tests prove the crash exists without the preload and is fixed with it (run in isolated
 * processes because the protobuf descriptor pool is a global singleton).
 */
final class TranscoderErrorDetailsPoolTest extends TestCase
{
    private const string FAILED_JOB_JSON = '{"name":"projects/1/locations/europe-west1/jobs/abc","state":"FAILED","error":{"code":3,"message":"atom atom0 does not have any inputs ([input0]) with an audio track","details":[{"@type":"type.googleapis.com/google.rpc.BadRequest","fieldViolations":[{"field":"job.config","description":"the input has no audio track"}]}]}}';

    #[RunInSeparateProcess]
    public function testParsingFailedJobErrorDetailsThrowsWithoutPreload(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/descriptor pool|Type was not found/');

        new Job()->mergeFromJsonString(self::FAILED_JOB_JSON);
    }

    #[RunInSeparateProcess]
    public function testPreloadAllowsParsingFailedJobErrorDetails(): void
    {
        Serializer::loadKnownMetadataTypes();

        $job = new Job();
        $job->mergeFromJsonString(self::FAILED_JOB_JSON);

        self::assertSame(ProcessingState::FAILED, $job->getState());
        self::assertStringContainsString('audio track', $job->getError()->getMessage());
    }
}
