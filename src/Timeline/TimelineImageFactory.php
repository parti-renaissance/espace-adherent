<?php

namespace AppBundle\Timeline;

use AppBundle\Entity\Timeline\Measure;
use AppBundle\Repository\Timeline\MeasureRepository;
use Symfony\Component\Translation\TranslatorInterface;

class TimelineImageFactory
{
    private const OUTPUT_IMAGE_NAME = 'timeline-image.jpg';

    private const TEXT_FONT_BOLD = 'Roboto-Bold.ttf';
    private const TEXT_FONT_REGULAR = 'Roboto-Regular.ttf';

    private const TEXT_SIZE_BOLD = 60;
    private const TEXT_SIZE_REGULAR = 40;

    private const TEXTS_COORDINATES = [
        Measure::STATUS_DONE => ['x' => 760, 'y' => 674],
        Measure::STATUS_IN_PROGRESS => ['x' => 760, 'y' => 772],
        Measure::STATUS_UPCOMING => ['x' => 760, 'y' => 870],
    ];

    private const CHART = [
        'center' => ['x' => 378, 'y' => 745],
        'innerRadius' => 151,
        'outerRadius' => 388,
    ];

    private $measureRepository;
    private $translator;
    private $cacheDirectory;
    private $webDirectory;

    public function __construct(
        MeasureRepository $measureRepository,
        TranslatorInterface $translator,
        string $cacheDirectory,
        string $webDirectory
    ) {
        $this->measureRepository = $measureRepository;
        $this->translator = $translator;
        $this->cacheDirectory = $cacheDirectory;
        $this->webDirectory = $webDirectory;
    }

    public function createImage(string $locale): string
    {
        $counts = $this->getMeasureCounts();

        $image = $this->createImageFromPng($this->getImageTemplatePath($locale));

        $this->drawTexts($image, $counts, $locale);
        $this->drawChart($image, $counts);

        $this->saveImage($image, $imagePath = $this->getOutputImagePath());

        return $imagePath;
    }

    private function drawTexts($image, array $counts, string $locale): void
    {
        $textColor = $this->createColor($image, 255, 255, 255);
        $boldFont = $this->getFontPath(self::TEXT_FONT_BOLD);
        $regularFont = $this->getFontPath(self::TEXT_FONT_REGULAR);

        foreach ($counts as $status => $count) {
            $this->drawText(
                $image,
                $count,
                $textColor,
                $boldFont,
                self::TEXT_SIZE_BOLD,
                self::TEXTS_COORDINATES[$status]['x'],
                self::TEXTS_COORDINATES[$status]['y']
            );

            $this->drawText(
                $image,
                $this->translate(sprintf('timeline.measure.status.%s', strtolower($status)), $locale),
                $textColor,
                $regularFont,
                self::TEXT_SIZE_REGULAR,
                self::TEXTS_COORDINATES[$status]['x'] + 20 + strlen($count) * 45,
                self::TEXTS_COORDINATES[$status]['y'] - 5
            );
        }
    }

    private function drawChartArc($image, int $radius, int $start, int $end, int $color): void
    {
        $this->drawArc($image, self::CHART['center']['x'], self::CHART['center']['y'], $radius, $start, $end, $color);
    }

    private function drawChart($image, array $counts): void
    {
        $total = array_sum($counts);

        $color1start = -90;
        $color1end = $color1start + ($counts[Measure::STATUS_DONE] / $total) * 360;
        $color2end = $color1end + ($counts[Measure::STATUS_IN_PROGRESS] / $total) * 360;

        $color1 = $this->createColor($image, 255, 234, 0);
        $color2 = $this->createColor($image, 189, 182, 81);
        $background = $this->createColor($image, 113, 129, 255);

        $this->drawChartArc($image, self::CHART['outerRadius'], $color1end, $color2end, $color2);
        $this->drawChartArc($image, self::CHART['outerRadius'], $color1start, $color1end, $color1);
        $this->drawChartArc($image, self::CHART['innerRadius'], 0, 360, $background);
    }

    private function getOutputImagePath(): string
    {
        return sprintf('%s/%s', $this->cacheDirectory, self::OUTPUT_IMAGE_NAME);
    }

    private function getImageTemplatePath(string $locale): string
    {
        return sprintf('%s/images/transformer-social-media-%s.png', $this->webDirectory, $locale);
    }

    private function getFontPath(string $fontName): string
    {
        return sprintf('%s/fonts/%s', $this->webDirectory, $fontName);
    }

    private function getMeasureCounts(): array
    {
        foreach (array_keys(self::TEXTS_COORDINATES) as $measureStatus) {
            $counts[$measureStatus] = $this->countMeasuresByStatus($measureStatus);
        }

        return $counts ?? [];
    }

    private function countMeasuresByStatus(string $status): int
    {
        return $this->measureRepository->countMeasuresByStatus($status);
    }

    private function translate(string $id, string $locale): string
    {
        return $this->translator->trans($id, [], 'messages', $locale);
    }

    private function createImageFromPng(string $path)
    {
        return imagecreatefrompng($path);
    }

    private function saveImage($image, string $path): void
    {
        imagejpeg($image, $path);
        imagedestroy($image);
    }

    private function createColor($image, int $red, int $green, int $blue): int
    {
        return imagecolorallocate($image, $red, $green, $blue);
    }

    private function drawText($image, string $text, int $color, string $font, int $size, int $x, int $y): void
    {
        imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
    }

    private function drawArc($image, int $x, int $y, int $radius, int $start, int $end, int $color): void
    {
        imagefilledarc($image, $x, $y, $radius, $radius, $start, $end, $color, IMG_ARC_PIE);
    }
}
