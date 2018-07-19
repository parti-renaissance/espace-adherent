<?php

namespace AppBundle\MediaGenerator;

use AppBundle\MediaGenerator\Command\MediaCommandInterface;

interface MediaGeneratorInterface
{
    public function generate(MediaCommandInterface $command): MediaContent;
}
