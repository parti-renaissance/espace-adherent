<?php

namespace AppBundle\Facebook;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Symfony\Component\Finder\Finder;

class ImageFilter
{
    private $imagine;
    private $path;

    public function __construct(ImagineInterface $imagine, string $path)
    {
        $this->imagine = $imagine;
        $this->path = $path;
    }

    /**
     * Applies watermarks on the given picture path
     * and returns an array of base64 encoded jpeg pictures.
     */
    public function applyWatermarks(string $picture): array
    {
        $picture = $this->imagine->open($picture);

        foreach ($this->generateWatermarks() as $watermark) {
            $generatedPictures[] = base64_encode($this->addWatermark($watermark, $picture)->get('jpeg'));
        }

        return $generatedPictures ?? [];
    }

    private function addWatermark(ImageInterface $watermark, ImageInterface $picture): ImageInterface
    {
        $watermark->resize($picture->getSize());
        $picture->paste($watermark, new Point(0, 0));

        return $picture;
    }

    /**
     * Generate an array of watermarks image for the given path.
     */
    private function generateWatermarks(): array
    {
        if (!is_dir($this->path)) {
            throw new \RuntimeException(sprintf('The %s directory does not exist.', $this->path));
        }

        $finder = new Finder();
        $finder->files()->in($this->path);

        if (0 === count($finder)) {
            throw new \RuntimeException(sprintf('No watermark file found in directory %s', $this->path));
        }

        foreach ($finder as $file) {
            $watermarks[] = $this->imagine->open($file->getRealPath());
        }

        return $watermarks ?? [];
    }
}
