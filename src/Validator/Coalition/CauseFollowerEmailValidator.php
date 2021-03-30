<?php

namespace App\Validator\Coalition;

use App\Entity\Coalition\CauseFollower;
use App\Repository\AdherentRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CauseFollowerEmailValidator extends ConstraintValidator
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CauseFollowerEmail) {
            throw new UnexpectedTypeException($constraint, CauseFollowerEmail::class);
        }

        if (!$value instanceof CauseFollower) {
            throw new UnexpectedValueException($value, CauseFollower::class);
        }

        if (!$email = $value->getEmailAddress()) {
            return;
        }

        $found = $this->adherentRepository->findOneByEmail($email);

        if ($found) {
            $this
                ->context
                ->buildViolation($constraint->messageAdherentExists)
                ->atPath($constraint->errorPath)
                ->addViolation()
            ;
        }
    }
}
