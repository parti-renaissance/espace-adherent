<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;

class InstanceOfExtension extends AbstractExtension
{
    public function getTests()
    {
        return [new \Twig_SimpleTest('instanceof', [$this, 'isInstanceOf'])];
    }

    public function isInstanceOf($object, string $class): bool
    {
        return $object instanceof $class;
    }
}
