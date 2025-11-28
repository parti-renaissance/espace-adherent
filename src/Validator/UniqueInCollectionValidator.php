<?php

declare(strict_types=1);

namespace App\Validator;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueInCollectionValidator extends ConstraintValidator
{
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueInCollection) {
            throw new UnexpectedTypeException($constraint, UniqueInCollection::class);
        }

        if (!$value) {
            return;
        }

        if (!$value instanceof Collection) {
            throw new UnexpectedValueException($value, Collection::class);
        }

        $propertyValues = [];
        foreach ($value as $key => $item) {
            $propertyValue = $constraint->propertyPath
                ? $this->propertyAccessor->getValue($item, $constraint->propertyPath)
                : $item;

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
