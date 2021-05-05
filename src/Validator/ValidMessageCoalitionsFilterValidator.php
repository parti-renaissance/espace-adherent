<?php

namespace App\Validator;

use App\Entity\AdherentMessage\Filter\CoalitionsFilter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidMessageCoalitionsFilterValidator extends ConstraintValidator
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof ValidMessageCoalitionsFilter) {
            throw new UnexpectedTypeException($constraint, ValidMessageCoalitionsFilter::class);
        }

        if (!$value instanceof CoalitionsFilter) {
            throw new UnexpectedValueException($value, CoalitionsFilter::class);
        }

        if (!$cause = $value->getCause()) {
            return;
        }

        if (!$user = $this->security->getUser()) {
            return;
        }

        if (!$cause->isApproved()) {
            $this->context
                ->buildViolation($constraint->invalidCauseStatus)
                ->atPath('cause')
                ->addViolation()
            ;
        }

        if ($cause->getAuthor() !== $user) {
            $this->context
                ->buildViolation($constraint->invalidAuthor)
                ->atPath('cause')
                ->addViolation()
            ;
        }
    }
}
