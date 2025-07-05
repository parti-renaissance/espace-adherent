<?php

namespace App\Validator;

use App\Repository\AdherentRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PublicIdValidator extends ConstraintValidator
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly string $patternPid,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PublicId) {
            throw new UnexpectedTypeException($constraint, PublicId::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match('#'.$this->patternPid.'#', $value)) {
            $this->context
                ->buildViolation($constraint->messageWrongFormat)
                ->addViolation()
            ;

            return;
        }

        if (!$this->adherentRepository->findByPublicId($value, true)) {
            $this->context
                ->buildViolation($constraint->messageNotFound)
                ->addViolation()
            ;
        }
    }
}
