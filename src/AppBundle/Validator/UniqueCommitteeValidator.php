<?php

namespace AppBundle\Validator;

use AppBundle\Committee\CommitteeCreationCommand;
use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeRepository;
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

        if (!$value instanceof CommitteeCreationCommand) {
            throw new UnexpectedTypeException($value, CommitteeCreationCommand::class);
        }

        $committee = $this->repository->findByName($value->name);

        if ($committee instanceof Committee) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ name }}', $value->name)
                ->atPath($constraint->errorPath)
                ->addViolation()
            ;
        }
    }
}
