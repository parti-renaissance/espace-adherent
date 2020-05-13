<?php

namespace App\MediaGenerator;

use Knp\Snappy\GeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

abstract class BaseSnappyGenerator implements MediaGeneratorInterface
{
    protected $mediaGenerator;
    protected $templateEngine;

    public function __construct(GeneratorInterface $mediaGenerator, EngineInterface $templateEngine)
    {
        $this->mediaGenerator = $mediaGenerator;
        $this->templateEngine = $templateEngine;
    }
}
