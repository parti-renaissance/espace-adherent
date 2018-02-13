<?php

namespace AppBundle\MediaGenerator\Image;

use AppBundle\MediaGenerator\BaseSnappyGenerator;
use AppBundle\MediaGenerator\Command\MediaCommandInterface;
use AppBundle\MediaGenerator\MediaContent;

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
