<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\BannedAdherentRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class BannedAdherentValidator extends ConstraintValidator
{
    public function __construct(private readonly BannedAdherentRepository $bannedAdherentRepository)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof BannedAdherent) {
            throw new UnexpectedTypeException($constraint, BannedAdherent::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->bannedAdherentRepository->countForEmail($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ email }}', $value)
                ->addViolation()
            ;
        }
    }
}
