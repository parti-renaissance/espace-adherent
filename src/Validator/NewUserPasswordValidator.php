<?php

namespace App\Validator;

use App\AdherentProfile\NewUserPasswordInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NewUserPasswordValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NewUserPassword) {
            throw new UnexpectedTypeException($constraint, NewUserPassword::class);
        }

        if (!$value) {
            return;
        }

        if (!$value instanceof NewUserPasswordInterface) {
            throw new UnexpectedValueException($value, NewUserPasswordInterface::class);
        }

        if (!$value->getNewPassword() && !$value->getNewPasswordConfirmation()) {
            return;
        }

        if ($value->getNewPassword() !== $value->getNewPasswordConfirmation()) {
            $this
                ->context
                ->buildViolation($constraint->notMatchingMessage)
                ->atPath('newPassword')
                ->addViolation()
            ;
        }
    }
}
