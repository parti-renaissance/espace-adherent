<?php

namespace App\Validator;

use App\Jecoute\GenderEnum;
use App\Membership\MembershipRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomGenderValidator extends ConstraintValidator
{
    /**
     * @param MembershipRequest       $value
     * @param CustomGender|Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomGender) {
            throw new UnexpectedTypeException($constraint, CustomGender::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof MembershipRequest) {
            throw new UnexpectedTypeException($value, MembershipRequest::class);
        }

        if (GenderEnum::OTHER !== $value->gender && !empty($value->customGender)) {
            $this->context
                ->buildViolation($constraint->messageInvalidChoice)
                ->atPath('gender')
                ->addViolation()
            ;
        }

        if (GenderEnum::OTHER === $value->gender && empty($value->customGender)) {
            $this->context
                ->buildViolation($constraint->messageNotBlank)
                ->atPath('customGender')
                ->addViolation()
            ;
        }
    }
}
