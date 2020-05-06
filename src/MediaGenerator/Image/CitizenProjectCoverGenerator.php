<?php

namespace App\MediaGenerator\Image;

use App\MediaGenerator\BaseSnappyGenerator;
use App\MediaGenerator\Command\MediaCommandInterface;
use App\MediaGenerator\MediaContent;

class CitizenProjectCoverGenerator extends BaseSnappyGenerator
{
    public function generate(MediaCommandInterface $command): MediaContent
    {
        $html = $this->templateEngine->render(
            'citizen_project/cover_image.html.twig',
            [
                'command' => $command,
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
