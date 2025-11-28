<?php

declare(strict_types=1);

namespace App\Validator;

use App\Committee\DTO\CommitteeCommand;
use App\Entity\Committee;
use App\Repository\CommitteeRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueCommitteeValidator extends ConstraintValidator
{
    private $repository;

    public function __construct(CommitteeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueCommittee) {
            throw new UnexpectedTypeException($constraint, UniqueCommittee::class);
        }

        if (!$value instanceof CommitteeCommand) {
            throw new UnexpectedValueException($value, CommitteeCommand::class);
        }

        $committee = $value->getCommittee();

        $found = $this->repository->findOneByName($value->name);
        if ($found instanceof Committee && (null === $committee || !$committee->equals($found))) {
            $this
                ->context
                ->buildViolation($constraint->messageName)
                ->atPath($constraint->errorPathName)
                ->addViolation()
            ;
        }

        $found = array_filter($this->repository->findApprovedByAddress($value->getAddress(), 2), function (Committee $a) use ($committee) {
            return !$committee || !$a->equals($committee);
        });

        if ($found) {
            $this
                ->context
                ->buildViolation($constraint->messageAddress)
                ->atPath($constraint->errorPathAddress)
                ->addViolation()
            ;
        }
    }
}
