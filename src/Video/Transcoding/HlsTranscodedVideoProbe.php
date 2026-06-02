<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

use App\Entity\Video;
use App\Utils\VideoUrlBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Probes the dimensions and duration of a transcoded video by parsing its own HLS output served from
 * our CDN: RESOLUTION from the master manifest, and the sum of the media playlist's #EXTINF durations.
 */
class HlsTranscodedVideoProbe implements TranscodedVideoProbeInterface
{
    public function __construct(
        private readonly VideoUrlBuilder $urlBuilder,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function probe(Video $video): VideoMediaInfo
    {
        $masterUrl = $this->urlBuilder->videoHlsUrl($video);

        if (null === $masterUrl) {
            return new VideoMediaInfo();
        }

        try {
            $master = $this->httpClient->request('GET', $masterUrl)->getContent();

            [$width, $height, $variantUri] = $this->parseMaster($master);

            $mediaPlaylist = null === $variantUri
                ? $master
                : $this->httpClient->request('GET', $this->resolveVariantUrl($masterUrl, $variantUri))->getContent();

            return new VideoMediaInfo($width, $height, $this->sumDurations($mediaPlaylist));
        } catch (\Throwable $exception) {
            $this->logger->warning('[Video probe] HLS probe failed.', ['url' => $masterUrl, 'error' => $exception->getMessage()]);

            return new VideoMediaInfo();
        }
    }

    /**
     * @return array{0: ?int, 1: ?int, 2: ?string} width, height, variant playlist URI of the largest variant
     */
    private function parseMaster(string $playlist): array
    {
        $width = null;
        $height = null;
        $variantUri = null;
        $bestArea = -1;
        $pending = null;

        foreach (preg_split('/\R/', $playlist) ?: [] as $rawLine) {
            $line = trim($rawLine);

            if ('' === $line) {
                continue;
            }

            if (str_starts_with($line, '#EXT-X-STREAM-INF')) {
                $pending = preg_match('/RESOLUTION=(\d+)x(\d+)/', $line, $matches) ? [(int) $matches[1], (int) $matches[2]] : [null, null];

                continue;
            }

            if (str_starts_with($line, '#')) {
                continue;
            }

            if (null !== $pending) {
                $area = (int) $pending[0] * (int) $pending[1];

                if ($area > $bestArea) {
                    $bestArea = $area;
                    $width = $pending[0];
                    $height = $pending[1];
                    $variantUri = $line;
                }

                $pending = null;
            }
        }

        return [$width, $height, $variantUri];
    }

    private function sumDurations(string $playlist): ?int
    {
        if (!preg_match_all('/#EXTINF:([\d.]+)/', $playlist, $matches)) {
            return null;
        }

        return (int) round(array_sum(array_map('floatval', $matches[1])));
    }

    private function resolveVariantUrl(string $masterUrl, string $variantUri): string
    {
        if (str_starts_with($variantUri, 'http://') || str_starts_with($variantUri, 'https://')) {
            return $variantUri;
        }

        return substr($masterUrl, 0, (int) strrpos($masterUrl, '/') + 1).$variantUri;
    }
}
