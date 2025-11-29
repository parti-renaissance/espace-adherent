<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ZoneTypeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ZoneType) {
            throw new UnexpectedTypeException($constraint, ZoneType::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Collection) {
            throw new UnexpectedValueException($value, Collection::class);
        }

        if (!$value->count()) {
            return;
        }

        $types = array_map(function (Zone $zone): string {
            return $zone->getType();
        }, $value->toArray());

        if (\count(array_intersect($types, $constraint->types)) !== \count($types)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
