<?php

namespace App\Validator;

use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Jecoute\GenderEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class JemarcheDataSurveyConstraintValidator extends ConstraintValidator
{
    /**
     * @param JemarcheDataSurvey $dataSurvey
     */
    public function validate($dataSurvey, Constraint $constraint)
    {
        if (!$constraint instanceof JemarcheDataSurveyConstraint) {
            throw new UnexpectedTypeException($constraint, JemarcheDataSurveyConstraint::class);
        }

        if (null === $dataSurvey) {
            return;
        }

        if (!$dataSurvey->getEmailAddress() && $dataSurvey->getAgreedToStayInContact()) {
            $this
                ->context
                ->buildViolation($constraint->emailAddressRequired)
                ->atPath('agreedToStayInContact')
                ->addViolation()
            ;
        }

        if (!$dataSurvey->getAgreedToStayInContact() && $dataSurvey->getAgreedToContactForJoin()) {
            $this
                ->context
                ->buildViolation($constraint->agreedToStayInContactRequired)
                ->atPath('agreedToStayInContact')
                ->addViolation()
            ;
        }

        if (GenderEnum::OTHER === $dataSurvey->getGender() && !$dataSurvey->getGenderOther()) {
            $this
                ->context
                ->buildViolation($constraint->genderOtherEmptyMessage)
                ->atPath('genderOther')
                ->addViolation()
            ;
        }

        if ($dataSurvey->getGenderOther() && GenderEnum::OTHER !== $dataSurvey->getGender()) {
            $this
                ->context
                ->buildViolation($constraint->genderChoiceOtherNotSelectedMessage)
                ->atPath('gender')
                ->addViolation()
            ;
        }
    }
}
