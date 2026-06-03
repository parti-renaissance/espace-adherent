<?php

declare(strict_types=1);

namespace Tests\App\Unit\Video\Transcoding;

use App\SocialNetwork\Video\Command\TranscodeSocialNetworkVideoCommand;
use App\Video\Transcoding\Command\RelaunchVideoTranscodingCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Guards backward compatibility of the capacityAttempt field: a message serialized before this field
 * existed (no capacity_attempt key) must still deserialize, defaulting the counter to 0, so in-flight
 * and dead-lettered messages replay cleanly. Uses the same snake_case name converter as the app
 * (config/packages/framework.php).
 */
final class CapacityAwareMessageSerializationTest extends TestCase
{
    public function testIngestCommandDefaultsCapacityAttemptAndIncrements(): void
    {
        self::assertSame(0, new TranscodeSocialNetworkVideoCommand(5, 'gs://x')->getCapacityAttempt());
        self::assertSame(1, new TranscodeSocialNetworkVideoCommand(5, 'gs://x')->withNextCapacityAttempt()->getCapacityAttempt());
    }

    public function testRelaunchCommandDefaultsCapacityAttemptAndIncrements(): void
    {
        self::assertSame(0, new RelaunchVideoTranscodingCommand('uuid')->getCapacityAttempt());
        self::assertSame(1, new RelaunchVideoTranscodingCommand('uuid')->withNextCapacityAttempt()->getCapacityAttempt());
    }

    public function testLegacyIngestPayloadWithoutCapacityAttemptDeserializesToZero(): void
    {
        /** @var TranscodeSocialNetworkVideoCommand $command */
        $command = $this->serializer()->denormalize(
            ['social_network_feed_video_id' => 5, 'source_uri' => 'gs://x'],
            TranscodeSocialNetworkVideoCommand::class,
        );

        self::assertSame(5, $command->socialNetworkFeedVideoId);
        self::assertSame('gs://x', $command->sourceUri);
        self::assertSame(0, $command->capacityAttempt);
    }

    public function testLegacyRelaunchPayloadWithoutCapacityAttemptDeserializesToZero(): void
    {
        /** @var RelaunchVideoTranscodingCommand $command */
        $command = $this->serializer()->denormalize(
            ['video_uuid' => '7ebd8666-8059-4bf2-afcd-8d08ade88af3'],
            RelaunchVideoTranscodingCommand::class,
        );

        self::assertSame('7ebd8666-8059-4bf2-afcd-8d08ade88af3', $command->videoUuid);
        self::assertSame(0, $command->capacityAttempt);
    }

    private function serializer(): Serializer
    {
        return new Serializer([new ObjectNormalizer(nameConverter: new CamelCaseToSnakeCaseNameConverter())]);
    }
}
