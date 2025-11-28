<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Jecoute\GenderEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class JemarcheDataSurveyConstraintValidator extends ConstraintValidator
{
    /**
     * @param JemarcheDataSurvey $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof JemarcheDataSurveyConstraint) {
            throw new UnexpectedTypeException($constraint, JemarcheDataSurveyConstraint::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value->getEmailAddress() && $value->getAgreedToStayInContact()) {
            $this
                ->context
                ->buildViolation($constraint->emailAddressRequired)
                ->atPath('agreedToStayInContact')
                ->addViolation()
            ;
        }

        if (!$value->getAgreedToStayInContact() && $value->getAgreedToContactForJoin()) {
            $this
                ->context
                ->buildViolation($constraint->agreedToStayInContactRequired)
                ->atPath('agreedToStayInContact')
                ->addViolation()
            ;
        }

        if (GenderEnum::OTHER === $value->getGender() && !$value->getGenderOther()) {
            $this
                ->context
                ->buildViolation($constraint->genderOtherEmptyMessage)
                ->atPath('genderOther')
                ->addViolation()
            ;
        }

        if ($value->getGenderOther() && GenderEnum::OTHER !== $value->getGender()) {
            $this
                ->context
                ->buildViolation($constraint->genderChoiceOtherNotSelectedMessage)
                ->atPath('gender')
                ->addViolation()
            ;
        }
    }
}
