<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class DoubleNewlineTextTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        if (null !== $value) {
            return preg_replace("/(\r?\n){3,}/", "\n\n", $value);
        }

        return $value;
    }
}
