<?php

namespace App\Validator;

use App\Entity\Image;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ImageValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ImageObjectValidator extends ImageValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ImageObject) {
            throw new UnexpectedTypeException($constraint, ImageObject::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Image) {
            throw new \InvalidArgumentException('Wrong type');
        }

        parent::validate($value->getFile(), $constraint);
    }
}
