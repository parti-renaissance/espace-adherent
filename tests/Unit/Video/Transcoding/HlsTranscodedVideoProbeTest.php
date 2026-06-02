<?php

declare(strict_types=1);

namespace Tests\App\Unit\Video\Transcoding;

use App\Entity\Video;
use App\Utils\VideoUrlBuilder;
use App\Video\Transcoding\HlsTranscodedVideoProbe;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HlsTranscodedVideoProbeTest extends TestCase
{
    private const string MASTER_URL = 'https://cdn.example/videos/uuid/master.m3u8';
    private const string VARIANT_URL = 'https://cdn.example/videos/uuid/hls-stream.m3u8';

    public function testParsesResolutionFromMasterAndDurationFromVariant(): void
    {
        $master = "#EXTM3U\n#EXT-X-STREAM-INF:BANDWIDTH=1153004,RESOLUTION=404x720,CODECS=\"avc1.4d001e\"\nhls-stream.m3u8\n";
        $variant = "#EXTM3U\n#EXT-X-TARGETDURATION:6\n#EXTINF:6.000000,\nseg0.ts\n#EXTINF:6.000000,\nseg1.ts\n#EXTINF:2.000000,\nseg2.ts\n#EXT-X-ENDLIST\n";

        $info = $this->probe([self::MASTER_URL => $master, self::VARIANT_URL => $variant]);

        self::assertSame(404, $info->width);
        self::assertSame(720, $info->height);
        self::assertSame(14, $info->duration);
    }

    public function testReturnsEmptyMediaInfoOnHttpError(): void
    {
        $urlBuilder = $this->createStub(VideoUrlBuilder::class);
        $urlBuilder->method('videoHlsUrl')->willReturn(self::MASTER_URL);

        $httpClient = $this->createStub(HttpClientInterface::class);
        $httpClient->method('request')->willThrowException(new \RuntimeException('network down'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');

        $info = new HlsTranscodedVideoProbe($urlBuilder, $httpClient, $logger)->probe(new Video());

        self::assertNull($info->width);
        self::assertNull($info->height);
        self::assertNull($info->duration);
    }

    public function testReturnsNullsWhenMasterHasNoVariantNorSegments(): void
    {
        $info = $this->probe([self::MASTER_URL => "#EXTM3U\n#EXT-X-ENDLIST\n"]);

        self::assertNull($info->width);
        self::assertNull($info->height);
        self::assertNull($info->duration);
    }

    /**
     * @param array<string, string> $responses
     */
    private function probe(array $responses): \App\Video\Transcoding\VideoMediaInfo
    {
        $urlBuilder = $this->createStub(VideoUrlBuilder::class);
        $urlBuilder->method('videoHlsUrl')->willReturn(self::MASTER_URL);

        $httpClient = $this->createStub(HttpClientInterface::class);
        $httpClient->method('request')->willReturnCallback(function (string $method, string $url) use ($responses): ResponseInterface {
            $response = $this->createStub(ResponseInterface::class);
            $response->method('getContent')->willReturn($responses[$url] ?? '');

            return $response;
        });

        return new HlsTranscodedVideoProbe($urlBuilder, $httpClient, $this->createStub(LoggerInterface::class))->probe(new Video());
    }
}
