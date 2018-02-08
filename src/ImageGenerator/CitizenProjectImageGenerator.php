<?php

namespace AppBundle\ImageGenerator;

use AppBundle\ImageGenerator\Command\CitizenProjectImageCommand;
use AppBundle\ImageGenerator\Command\ImageCommandInterface;
use Intervention\Image\AbstractFont;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class CitizenProjectImageGenerator extends AbstractImageGenerator
{
    private const OPACITY_LEVEL = .85;
    private const TITLE_FONT_SIZE = 50;
    private const TEXT_FONT_SIZE = 20;
    private const TEXT_COLOR = 'fff';
    /**
     * @var string
     */
    private $badgeImagePath;

    /**
     * @var string
     */
    private $titleFontPath;

    /**
     * @var string
     */
    private $textFontPath;

    public function __construct(
        ImageManager $imageManager,
        string $badgeImagePath,
        string $titleFontPath,
        string $textFontPath
    ) {
        parent::__construct($imageManager);

        $this->badgeImagePath = $badgeImagePath;
        $this->titleFontPath = $titleFontPath;
        $this->textFontPath = $textFontPath;
    }

    public function generate(ImageCommandInterface $command): Image
    {
        /** @var CitizenProjectImageCommand $command */
        $image = $this->imageManager->make($command->getImagePath());

        // Apply color mask on source image
        $maskImage = $this->imageManager->canvas(
            $image->width(),
            $image->height(),
            $this->getRGBAColor($command->getBackgroundColor())
        );
        $maskImage->encode('png');
        $image->insert($maskImage);

        // Insert the text
        $title = $this->getTitle($command->getCitizenProjectTitle());
        $image->text(
            $title,
            $image->width() / 2,
            $image->height() / 2,
            function (AbstractFont $font) {
                $font->file($this->titleFontPath);
                $font->size(self::TITLE_FONT_SIZE);
                $font->color(self::TEXT_COLOR);
                $font->valign('middle');
                $font->align('center');
            }
        );

        $image->text(
            $this->getCityName($command),
            $image->width() / 2,
            $this->calculateCityLineYPosition($image, $title),
            function (AbstractFont $font) {
                $font->file($this->textFontPath);
                $font->size(self::TEXT_FONT_SIZE);
                $font->color(self::TEXT_COLOR);
                $font->valign('middle');
                $font->align('center');
            }
        );

        // Insert the badge
        $badgeImage = $this->imageManager->make($this->badgeImagePath);
        $badgeImage->heighten((int) $image->height() * 0.4);
        $image->insert($badgeImage, 'top-left', 0.65 * $image->width(), 20);

        return $image->encode('png');
    }

    private function getRGBAColor($hexColor): array
    {
        $int = \hexdec($hexColor);

        return [
            0xFF & ($int >> 0x10),
            0xFF & ($int >> 0x8),
            0xFF & $int,
            self::OPACITY_LEVEL,
        ];
    }

    private function getTitle(string $text): string
    {
        return \wordwrap(\mb_strtoupper($text), 20);
    }

    private function getCityName(CitizenProjectImageCommand $command): string
    {
        return \sprintf('(%s) %s', $command->getDepartmentCode(), \mb_strtoupper($command->getCity()));
    }

    private function calculateCityLineYPosition(Image $image, string $title): int
    {
        return \round($image->height() / 2 + (\mb_substr_count($title, "\n") + 2) * self::TITLE_FONT_SIZE / 2);
    }
}
