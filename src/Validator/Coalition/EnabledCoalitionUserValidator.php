<?php

namespace App\Validator\Coalition;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class EnabledCoalitionUserValidator extends ConstraintValidator
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof EnabledCoalitionUser) {
            throw new UnexpectedTypeException($constraint, EnabledCoalitionUser::class);
        }

        if (!$value instanceof Adherent) {
            throw new UnexpectedValueException($value, Adherent::class);
        }

        $found = $this->adherentRepository->findEnabledCoalitionUserByUuid($value->getUuidAsString());

        if (!$found) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
