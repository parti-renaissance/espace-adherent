<?php

declare(strict_types=1);

namespace App\Twig;

use App\Utils\VideoUrlBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VideoUrlExtension extends AbstractExtension
{
    public function __construct(private readonly VideoUrlBuilder $urlBuilder)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('video_hls_url', $this->urlBuilder->videoHlsUrl(...)),
            new TwigFunction('video_preview_url', $this->urlBuilder->videoPreviewUrl(...)),
            new TwigFunction('video_thumbnail_url', $this->urlBuilder->videoThumbnailUrl(...)),
        ];
    }
}
