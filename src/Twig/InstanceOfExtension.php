<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class InstanceOfExtension extends AbstractExtension
{
    public function getTests()
    {
        return [new TwigTest('instanceof', [$this, 'isInstanceOf'])];
    }

    public function isInstanceOf($object, string $class): bool
    {
        return $object instanceof $class;
    }
}
