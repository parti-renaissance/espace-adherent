<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\AdherentRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AdherentUuidValidator extends ConstraintValidator
{
    private AdherentRepository $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AdherentUuid) {
            throw new UnexpectedTypeException($constraint, AdherentUuid::class);
        }

        if (null === $value) {
            return;
        }

        if (!$this->adherentRepository->findOneByUuid($value)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation()
            ;
        }
    }
}
