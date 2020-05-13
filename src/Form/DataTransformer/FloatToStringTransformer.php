<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FloatToStringTransformer implements DataTransformerInterface
{
    public function transform($value): string
    {
        if (null === $value) {
            return '';
        }

        if (!\is_float($value)) {
            throw new TransformationFailedException('Expected a float.');
        }

        return sprintf('%.2f', $value);
    }

    public function reverseTransform($value): float
    {
        if (null === $value) {
            return 0;
        }

        if (!\is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        return \floatval($value);
    }
}
