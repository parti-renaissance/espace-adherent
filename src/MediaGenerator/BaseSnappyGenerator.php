<?php

namespace App\MediaGenerator;

use Knp\Snappy\GeneratorInterface;
use Twig\Environment;

abstract class BaseSnappyGenerator implements MediaGeneratorInterface
{
    protected $mediaGenerator;
    protected $templateEngine;

    public function __construct(GeneratorInterface $mediaGenerator, Environment $templateEngine)
    {
        $this->mediaGenerator = $mediaGenerator;
        $this->templateEngine = $templateEngine;
    }
}
