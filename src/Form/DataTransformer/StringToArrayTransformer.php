<?php

namespace AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class StringToArrayTransformer implements DataTransformerInterface
{
    private $separator;

    public function __construct(string $separator = ',')
    {
        $this->separator = $separator;
    }

    public function transform($value)
    {
        return implode($this->separator, (array) $value);
    }

    public function reverseTransform($value)
    {
        return array_map('trim', explode($this->separator, $value));
    }
}
