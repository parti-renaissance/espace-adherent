<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Repository\BannedAdherentRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BannedAdherentValidator extends ConstraintValidator
{
    private $bannedAdherentRepository;

    public function __construct(BannedAdherentRepository $bannedAdherentRepository)
    {
        $this->bannedAdherentRepository = $bannedAdherentRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BannedAdherent) {
            throw new UnexpectedTypeException($constraint, BannedAdherent::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $isBanned = $this->bannedAdherentRepository->findOneBy([
            'uuid' => Adherent::createUuid($value),
        ]);

        if ($isBanned) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ email }}', $value)
                ->addViolation()
            ;
        }
    }
}
