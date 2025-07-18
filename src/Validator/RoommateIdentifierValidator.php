<?php

namespace App\Validator;

use App\PublicId\MeetingInscriptionPublicIdGenerator;
use App\Repository\AdherentRepository;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RoommateIdentifierValidator extends ConstraintValidator
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly string $patternPid,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof RoommateIdentifier) {
            throw new UnexpectedTypeException($constraint, RoommateIdentifier::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match('#'.$this->patternPid.'#', $value) && !preg_match('#'.MeetingInscriptionPublicIdGenerator::PATTERN.'#', $value)) {
            $this->context
                ->buildViolation($constraint->messageWrongFormat)
                ->addViolation()
            ;

            return;
        }

        if (
            (preg_match('#'.$this->patternPid.'#', $value) && !$this->adherentRepository->findByPublicId($value, true))
            || (preg_match('#'.MeetingInscriptionPublicIdGenerator::PATTERN.'#', $value) && !$this->eventInscriptionRepository->findByPublicId($value))
        ) {
            $this->context
                ->buildViolation($constraint->messageNotFound)
                ->addViolation()
            ;
        }
    }
}
