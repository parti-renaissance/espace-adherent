<?php

namespace AppBundle\MediaGenerator;

use AppBundle\MediaGenerator\Command\MediaCommandInterface;

interface MediaGeneratorInterface
{
    /**
     * @param MediaCommandInterface $command
     *
     * @return MediaContent
     */
    public function generate(MediaCommandInterface $command): MediaContent;
}
