<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class StringToArrayTransformer implements DataTransformerInterface
{
    public const SEPARATOR_COMMA = ',';
    public const SEPARATOR_SEMICOLON = ';';

    private $separator;

    public function __construct(string $separator = self::SEPARATOR_COMMA)
    {
        $this->separator = $separator;
    }

    public function transform($value)
    {
        return implode($this->separator, (array) $value);
    }

    public function reverseTransform($value)
    {
        return array_filter(array_map('trim', explode($this->separator, $value)));
    }
}
