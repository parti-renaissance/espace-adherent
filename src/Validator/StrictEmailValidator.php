<?php

namespace App\Validator;

use Egulias\EmailValidator\EmailValidator as EguliasEmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\NoRFCWarningsValidation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class StrictEmailValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof StrictEmail) {
            throw new UnexpectedTypeException($constraint, StrictEmail::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $validator = new EguliasEmailValidator();

        if (!$validator->isValid($value, new MultipleValidationWithAnd([new NoRFCWarningsValidation(), new DNSCheckValidation()]))) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ email }}', $value)
                ->addViolation()
            ;
        }
    }
}
