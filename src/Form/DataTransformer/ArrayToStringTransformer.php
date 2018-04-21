<?php

namespace AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class ArrayToStringTransformer implements DataTransformerInterface
{
    public function transform($tagsAsArray)
    {
        return $tagsAsArray ? implode(',', $tagsAsArray) : '';
    }

    public function reverseTransform($tagsAsString)
    {
        return array_map('trim', explode(',', $tagsAsString));
    }
}
