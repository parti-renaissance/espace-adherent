<?php

namespace App\Validator\Assessor;

use App\Entity\AssessorRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AssessorDepartmentValidator extends ConstraintValidator
{
    /**
     * @param AssessorRequest $value
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AssessorDepartment) {
            throw new UnexpectedTypeException($constraint, AssessorDepartment::class);
        }

        $assessorPostalCode = $value->getAssessorPostalCode();

        if (null === $assessorPostalCode) {
            return;
        }

        if (substr($value->getAssessorPostalCode(), 0, 2) !== substr($value->getPostalCode(), 0, 2)) {
            $this->context->buildViolation($constraint->invalidAssessorDepartmentCity)
                ->atPath('assessorPostalCode')
                ->addViolation()
            ;
        }
    }
}
