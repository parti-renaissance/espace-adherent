<?php

declare(strict_types=1);

namespace Tests\App\Unit\Video\Transcoding;

use App\Entity\Video;
use App\Video\Transcoding\TranscodingJobConfigFactory;
use Google\Cloud\Video\Transcoder\V1\Manifest\ManifestType;
use PHPUnit\Framework\TestCase;

final class TranscodingJobConfigFactoryTest extends TestCase
{
    public function testCreateProducesPreviewHlsAndThumbnailOutputsWithExpectedFileNames(): void
    {
        $config = new TranscodingJobConfigFactory()->create();

        // Video + audio elementary streams.
        self::assertCount(2, $config->getElementaryStreams());

        $muxByKey = [];
        foreach ($config->getMuxStreams() as $mux) {
            $muxByKey[$mux->getKey()] = $mux;
        }

        // MP4 preview: key 'preview' + container 'mp4' must yield preview.mp4 (not preview.mp4.mp4).
        self::assertArrayHasKey('preview', $muxByKey);
        self::assertSame('mp4', $muxByKey['preview']->getContainer());
        self::assertSame(Video::FILE_NAME_PREVIEW, $muxByKey['preview']->getFileName());

        // HLS segments mux stream.
        self::assertArrayHasKey('hls-stream', $muxByKey);
        self::assertSame('ts', $muxByKey['hls-stream']->getContainer());

        // HLS master manifest.
        $manifests = $config->getManifests();
        self::assertCount(1, $manifests);
        self::assertSame(Video::FILE_NAME_HLS, $manifests[0]->getFileName());
        self::assertSame(ManifestType::HLS, $manifests[0]->getType());

        // Thumbnail sprite sheet -> thumbnail0000000000.jpeg.
        $spriteSheets = $config->getSpriteSheets();
        self::assertCount(1, $spriteSheets);
        self::assertSame('thumbnail', $spriteSheets[0]->getFilePrefix());
    }

    public function testCreateWithoutAudioOmitsAudioStreamAndMapping(): void
    {
        $config = new TranscodingJobConfigFactory()->create(false);

        // Only the video elementary stream remains.
        $elementaryStreams = $config->getElementaryStreams();
        self::assertCount(1, $elementaryStreams);
        self::assertSame('video-stream', $elementaryStreams[0]->getKey());

        // No mux stream references an audio key (which would be orphan and fail the job).
        foreach ($config->getMuxStreams() as $mux) {
            self::assertSame(['video-stream'], iterator_to_array($mux->getElementaryStreams()));
        }

        // Manifest and thumbnail are unaffected.
        self::assertCount(1, $config->getManifests());
        self::assertCount(1, $config->getSpriteSheets());
    }
}
