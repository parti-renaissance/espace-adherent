<?php

namespace AppBundle\MediaGenerator\Image;

use AppBundle\MediaGenerator\BaseSnappyGenerator;
use AppBundle\MediaGenerator\Command\CitizenProjectImageCommand;
use AppBundle\MediaGenerator\Command\MediaCommandInterface;
use AppBundle\MediaGenerator\MediaContent;
use Knp\Snappy\GeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

class CitizenProjectCoverGenerator extends BaseSnappyGenerator
{
    private $badgeImagePath;

    public function __construct(
        GeneratorInterface $imageGenerator,
        EngineInterface $templateEngine,
        string $badgeImagePath
    ) {
        parent::__construct($imageGenerator, $templateEngine);

        $this->badgeImagePath = $badgeImagePath;
    }

    public function generate(MediaCommandInterface $command): MediaContent
    {
        /** @var CitizenProjectImageCommand $command */
        $html = $this->templateEngine->render(
            'citizen_project/cover_image.html.twig',
            [
                'command' => $command,
                'badge_path' => $this->badgeImagePath,
            ]
        );

        $imageContent = $this->mediaGenerator->getOutputFromHtml(
            $html,
            [
                'format' => 'png',
                'quality' => 100,
                'width' => 820,
            ]
        );

        return new MediaContent($imageContent, 'image/png', \strlen($imageContent));
    }
}
