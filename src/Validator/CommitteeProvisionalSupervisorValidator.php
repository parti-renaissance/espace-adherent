<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CommitteeProvisionalSupervisorValidator extends ConstraintValidator
{
    private $electedRepresentativeRepository;

    public function __construct(ElectedRepresentativeRepository $electedRepresentativeRepository)
    {
        $this->electedRepresentativeRepository = $electedRepresentativeRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CommitteeProvisionalSupervisor) {
            throw new UnexpectedTypeException($constraint, CommitteeProvisionalSupervisor::class);
        }

        if (!$value) {
            return;
        }

        if (!$value instanceof Adherent) {
            throw new UnexpectedValueException($value, Adherent::class);
        }

        if ($value->getGender() !== $constraint->gender) {
            $this
                ->context
                ->buildViolation($constraint->notValidGenderMessage)
                ->atPath($constraint->errorPath)
                ->addViolation()
            ;
        }

        if ($value->isMinor()
            || $value->isSupervisor()
            || $this->electedRepresentativeRepository->hasActiveParliamentaryMandate($value)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->addViolation()
            ;
        }
    }
}
