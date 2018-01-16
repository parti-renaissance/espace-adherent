<?php

namespace AppBundle\Timeline;

use AppBundle\Entity\Timeline\Measure;
use AppBundle\Repository\Timeline\MeasureRepository;

class TimelineImageFactory
{
    private const IMAGE_TEMPLATE_NAME = 'timeline-blank.png';
    private const OUTPUT_IMAGE_NAME = 'timeline-image.jpg';

    private const TEXT_FONT = 'gillsans-bold.ttf';
    private const TEXT_SIZE = 25;

    private const TEXTS_TO_ADD = [
        Measure::STATUS_DONE => ['x' => 110, 'y' => 343],
        Measure::STATUS_IN_PROGRESS => ['x' => 280, 'y' => 343],
        Measure::STATUS_UPCOMING => ['x' => 555, 'y' => 343]
    ];

    private const BAR_X1 = 89;
    private const BAR_X2 = 725;
    private const BAR_Y1 = 276;
    private const BAR_Y2 = 298;

    private $measureRepository;
    private $cacheDirectory;
    private $webDirectory;

    public function __construct(MeasureRepository $measureRepository, string $cacheDirectory, string $webDirectory)
    {
        $this->measureRepository = $measureRepository;
        $this->cacheDirectory = $cacheDirectory;
        $this->webDirectory = $webDirectory;
    }

    public function createImage(): string
    {
        $imageRessource = imagecreatefrompng($this->getImageTemplatePath());

        $white = imagecolorallocate($imageRessource, 255, 255, 255);
        $color1 = imagecolorallocate($imageRessource, 255, 234, 0);
        $color2 = imagecolorallocate($imageRessource, 189, 182, 81);

        $fontPath = $this->getFontPath();
        $textsToAdd = $this->getTextsToAdd();

        foreach ($textsToAdd as $text) {
            imagettftext($imageRessource, self::TEXT_SIZE, 0, $text['x'], $text['y'], $white, $fontPath, $text['count']);
        }

        $barLength = self::BAR_X2 - self::BAR_X1;
        $color1Start = self::BAR_X1;
        $color1End = $color1Start + $barLength * $textsToAdd[Measure::STATUS_DONE]['ratio'];
        $color2Start = $color1End + 1;
        $color2End = $color2Start + $barLength * $textsToAdd[Measure::STATUS_IN_PROGRESS]['ratio'];

        imagefilledrectangle($imageRessource, $color1Start, self::BAR_Y1, $color1End, self::BAR_Y2, $color1);
        imagefilledrectangle($imageRessource, $color2Start, self::BAR_Y1, $color2End, self::BAR_Y2, $color2);

        imagejpeg($imageRessource, $imagePath = $this->getOutputImagePath());
        imagedestroy($imageRessource);

        return $imagePath;
    }

    private function getOutputImagePath(): string
    {
        return sprintf('%s/%s', $this->cacheDirectory, self::OUTPUT_IMAGE_NAME);
    }

    private function getImageTemplatePath(): string
    {
        return sprintf('%s/images/%s', $this->webDirectory, self::IMAGE_TEMPLATE_NAME);
    }

    private function getFontPath(): string
    {
        return sprintf('%s/fonts/%s', $this->webDirectory, self::TEXT_FONT);
    }

    private function getTextsToAdd(): array
    {
        $texts = [];
        $total = 0;
        foreach (self::TEXTS_TO_ADD as $measureStatus => $textToAdd) {
            $texts[$measureStatus] = [
                'x' => $textToAdd['x'],
                'y' => $textToAdd['y'],
                'count' => $count = $this->countMeasuresByStatus($measureStatus),
            ];

            $total += $count;
        }

        foreach ($texts as $measureStatus => $text) {
            $texts[$measureStatus]['ratio'] = $text['count'] / $total;
        }

        return $texts;
    }

    private function countMeasuresByStatus(string $status): int
    {
        return $this->measureRepository->countMeasuresByStatus($status);
    }
}
