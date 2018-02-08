<?php

namespace AppBundle\ImageGenerator\Command;

interface ImageCommandInterface
{
    public function getImagePath(): string;
}
