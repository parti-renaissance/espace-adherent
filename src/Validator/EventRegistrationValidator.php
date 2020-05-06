<?php

namespace App\Validator;

use App\Repository\EventRegistrationRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EventRegistrationValidator extends ConstraintValidator
{
    private $repository;

    public function __construct(EventRegistrationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof EventRegistration) {
            throw new UnexpectedTypeException($constraint, EventRegistration::class);
        }

        if ($this->repository->isAlreadyRegistered($value->getEmailAddress(), $value->getEvent())) {
            $this->context->buildViolation($constraint->errorAlreadyExists)->addViolation();
        }
    }
}
