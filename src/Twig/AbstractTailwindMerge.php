<?php

namespace App\Twig;

use TailwindMerge\TailwindMerge;

abstract class AbstractTailwindMerge
{
    public ?string $class = null;
    private TailwindMerge $tw;

    public function __construct()
    {
        $this->tw = TailwindMerge::instance();
    }

    public function getTw(...$classes): string
    {
        return $this->tw->merge([...$classes, $this->class]);
    }
}
