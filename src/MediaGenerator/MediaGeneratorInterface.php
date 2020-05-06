<?php

namespace App\MediaGenerator;

use App\MediaGenerator\Command\MediaCommandInterface;

interface MediaGeneratorInterface
{
    public function generate(MediaCommandInterface $command): MediaContent;
}
