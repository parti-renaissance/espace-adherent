<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class DoubleNewlineTextTransformer implements DataTransformerInterface
{
    public function transform($value): mixed
    {
        return $value;
    }

    public function reverseTransform($value): mixed
    {
        if (null !== $value) {
            return preg_replace("/(\r?\n){3,}/", "\n\n", $value);
        }

        return $value;
    }
}
