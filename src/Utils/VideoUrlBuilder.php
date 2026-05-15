<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Video;

class VideoUrlBuilder
{
    public function __construct(private readonly string $mediaCdnBaseUrl)
    {
    }

    public function videoHlsUrl(Video $video): ?string
    {
        return $this->build($video->mediaPath, Video::FILE_NAME_HLS);
    }

    public function videoPreviewUrl(Video $video): ?string
    {
        return $this->build($video->mediaPath, Video::FILE_NAME_PREVIEW);
    }

    public function videoThumbnailUrl(Video $video): ?string
    {
        return $this->build($video->mediaPath, Video::FILE_NAME_THUMBNAIL);
    }

    private function build(?string $path, ?string $file = null): ?string
    {
        if (null === $path) {
            return null;
        }

        $url = rtrim($this->mediaCdnBaseUrl, '/').'/'.trim($path, '/');

        return null === $file ? $url : $url.'/'.$file;
    }
}
