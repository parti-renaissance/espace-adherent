<?php

namespace AppBundle\ImageGenerator;

use AppBundle\ImageGenerator\Command\CitizenProjectImageCommand;
use AppBundle\ImageGenerator\Command\ImageCommandInterface;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Knp\Snappy\GeneratorInterface;

class EmojiImageGenerator extends AbstractImageGenerator
{
    public const EMOJI_WIDTH = 200;
    public const EMOJI_HEIGHT = 200;

    /**
     * @var GeneratorInterface
     */
    private $emojiClient;

    public function __construct(ImageManager $imageManager, $emojiClient)
    {
        parent::__construct($imageManager);

        $this->emojiClient = $emojiClient;
    }

    public function generate(ImageCommandInterface $command): Image
    {
        /** @var CitizenProjectImageCommand $command */
//        $this->emojiClient->emojiSize = 128;
//        $this->emojiClient->spriteSize = 64;
//        $imageContent = $this->emojiClient->toImage($command->getEmoji());
//
//        dump($imageContent);
        $html = '<!Doctype html>
<html>
        <head>
            <meta charset="UTF-8"/>
            <style type="text/css">
                @font-face {
                    font-family:"testfont";
                    src: url("/app/web/fonts/NotoColorEmoji2.ttf");
                }
            </style>
        </head>
        <body style="width: 128px; height: 128px;">
            <div style="font-family: testfont; color: red;font-size: 120px;">'. $command->getEmoji() .'</div>
        </body>
        </html>
        ';
        dump($html);
        $result = $this->emojiClient->getOutputFromHtml($html);
        return $this->imageManager->make($result)->encode('png');

        return $this->imageManager->make('/app/app/data/static/badge/badge.jpeg')->encode('png');
    }
}
