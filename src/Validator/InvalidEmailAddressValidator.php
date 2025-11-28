<?php

declare(strict_types=1);

namespace App\Validator;

use App\InvalidEmailAddress\HashGenerator;
use App\Repository\InvalidEmailAddressRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class InvalidEmailAddressValidator extends ConstraintValidator
{
    private InvalidEmailAddressRepository $repository;
    private HashGenerator $hashGenerator;

    public function __construct(InvalidEmailAddressRepository $repository, HashGenerator $hashGenerator)
    {
        $this->repository = $repository;
        $this->hashGenerator = $hashGenerator;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof InvalidEmailAddress) {
            throw new UnexpectedTypeException($constraint, InvalidEmailAddress::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->repository->count(['emailHash' => $this->hashGenerator->generate($value)])) {
            $this->context->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
