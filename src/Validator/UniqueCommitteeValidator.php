<?php

namespace App\Validator;

use App\Committee\CommitteeCommand;
use App\Entity\Committee;
use App\Repository\CommitteeRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueCommitteeValidator extends ConstraintValidator
{
    private $repository;

    public function __construct(CommitteeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueCommittee) {
            throw new UnexpectedTypeException($constraint, UniqueCommittee::class);
        }

        if (!$value instanceof CommitteeCommand) {
            throw new UnexpectedTypeException($value, CommitteeCommand::class);
        }

        $found = $this->repository->findOneByName($value->name);
        if (!$found instanceof Committee) {
            return;
        }

        $committee = $value->getCommittee();

        if ($committee instanceof Committee && $committee->equals($found)) {
            return;
        }

        $this
            ->context
            ->buildViolation($constraint->message)
            ->setParameter('{{ name }}', $value->name)
            ->atPath($constraint->errorPath)
            ->addViolation()
        ;
    }
}
