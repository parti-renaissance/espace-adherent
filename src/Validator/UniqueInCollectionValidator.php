<?php

namespace App\Validator;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueInCollectionValidator extends ConstraintValidator
{
    private $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueInCollection) {
            throw new UnexpectedTypeException($constraint, UniqueInCollection::class);
        }

        if (!$value) {
            return;
        }

        if (!$value instanceof Collection) {
            throw new UnexpectedTypeException($value, Collection::class);
        }

        $propertyValues = [];
        foreach ($value as $key => $item) {
            $propertyValue = $constraint->propertyPath
                ? $this->propertyAccessor->getValue($item, $constraint->propertyPath)
                : $item
            ;

            if (\in_array($propertyValue, $propertyValues, true)) {
                $this
                    ->context
                    ->buildViolation($constraint->message)
                    ->atPath("[$key]")
                    ->setParameter('%value%', $propertyValue)
                    ->addViolation()
                ;

                return;
            }

            $propertyValues[] = $propertyValue;
        }
    }
}
