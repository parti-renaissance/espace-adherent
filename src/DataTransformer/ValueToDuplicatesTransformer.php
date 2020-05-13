<?php

namespace App\DataTransformer;

use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\DataTransformer\ValueToDuplicatesTransformer as BaseValueToDuplicatesTransformer;

/**
 * This class is an override of ValueToDuplicatesTransformer used in RepeatedType
 * It will have a fix in sf code soon to fix it with validation fields
 * by going through validation constraint instead of throwing an exception.
 */
class ValueToDuplicatesTransformer extends BaseValueToDuplicatesTransformer
{
    public function reverseTransform($array)
    {
        if (!\is_array($array)) {
            throw new TransformationFailedException('Expected an array.');
        }

        return current($array);
    }
}
