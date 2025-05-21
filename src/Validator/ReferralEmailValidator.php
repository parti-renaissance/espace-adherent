<?php

namespace App\Validator;

use App\Repository\AdherentRepository;
use App\Repository\BannedAdherentRepository;
use App\Repository\ReferralRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ReferralEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly BannedAdherentRepository $bannedAdherentRepository,
        private readonly ReferralRepository $referralRepository,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ReferralEmail) {
            throw new UnexpectedTypeException($constraint, ReferralEmail::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (
            $this->bannedAdherentRepository->countForEmail($value)
            || $this->referralRepository->isEmailReported($value)
            || (
                ($adherent = $this->adherentRepository->findOneByEmail($value))
                && ($adherent->isDisabled() || $adherent->isRenaissanceAdherent())
            )
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
