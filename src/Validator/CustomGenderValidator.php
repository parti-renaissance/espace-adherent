<?php

namespace App\Validator;

use App\Jecoute\GenderEnum;
use App\Membership\MembershipRequest\MembershipCustomGenderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CustomGenderValidator extends ConstraintValidator
{
    /**
     * @param MembershipCustomGenderInterface $value
     * @param CustomGender|Constraint         $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomGender) {
            throw new UnexpectedTypeException($constraint, CustomGender::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof MembershipCustomGenderInterface) {
            throw new UnexpectedValueException($value, MembershipCustomGenderInterface::class);
        }

        if (GenderEnum::OTHER !== $value->getGender() && !empty($value->getCustomGender())) {
            $this->context
                ->buildViolation($constraint->messageInvalidChoice)
                ->atPath('gender')
                ->addViolation()
            ;
        }

        if (GenderEnum::OTHER === $value->getGender() && empty($value->getCustomGender())) {
            $this->context
                ->buildViolation($constraint->messageNotBlank)
                ->atPath('customGender')
                ->addViolation()
            ;
        }
    }
}
