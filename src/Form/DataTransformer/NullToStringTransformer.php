<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/*
 * Enforces string representation of the model by explicit reverse transformation.
 *
 * This is a hack because when there is no transformer Symfony considers an empty string as null,
 * and this cannot be done via a FormEvents::SUBMIT listener.
 */
class NullToStringTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return $value; // no-op
    }

    public function reverseTransform($value)
    {
        if (null === $value) {
            return '';
        }

        return $value;
    }
}
