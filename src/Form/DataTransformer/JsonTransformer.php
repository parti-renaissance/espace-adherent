<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class JsonTransformer implements DataTransformerInterface
{
    public function transform(mixed $value)
    {
        return json_encode($value ?: [], \JSON_PRETTY_PRINT);
    }

    public function reverseTransform(mixed $value)
    {
        if ($value) {
            try {
                return json_decode($value, true, flags: \JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new TransformationFailedException('Invalid JSON format.');
            }
        }

        return [];
    }
}
