<?php

namespace AppBundle\Validator;

use AppBundle\Group\GroupCommand;
use AppBundle\Entity\Group;
use AppBundle\Repository\GroupRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueGroupValidator extends ConstraintValidator
{
    private $repository;

    public function __construct(GroupRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueGroup) {
            throw new UnexpectedTypeException($constraint, UniqueGroup::class);
        }

        if (!$value instanceof GroupCommand) {
            throw new UnexpectedTypeException($value, GroupCommand::class);
        }

        $found = $this->repository->findOneByName($value->name);
        if (!$found instanceof Group) {
            return;
        }

        $Group = $value->getGroup();

        if ($Group instanceof Group && $Group->equals($found)) {
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
