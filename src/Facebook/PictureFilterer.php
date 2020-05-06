<?php

namespace App\Facebook;

use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;

class PictureFilterer
{
    private $imagine;

    public function __construct(ImagineInterface $imagine)
    {
        $this->imagine = $imagine;
    }

    public function applyFilter(string $pictureData, string $filterData): string
    {
        $picture = $this->imagine->load($pictureData);
        $filter = $this->imagine->load($filterData);

        $filter->resize($picture->getSize());
        $picture->paste($filter, new Point(0, 0));

        return $picture->get('jpeg', [
            'jpeg_quality' => 80,
        ]);
    }
}
