<?php

namespace App\Validator\AdherentFormation;

use App\Entity\AdherentFormation\Formation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class FormationContentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FormationContent) {
            throw new UnexpectedTypeException($constraint, FormationContent::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Formation) {
            throw new UnexpectedValueException($value, Formation::class);
        }

        if (!$value->getFile() && !$value->getLink()) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->addViolation()
            ;
        }
    }
}
