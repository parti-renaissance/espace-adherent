<?php

namespace AppBundle\Validator;

use AppBundle\CitizenProject\CitizenProjectCommand;
use AppBundle\Entity\CitizenProject;
use AppBundle\Repository\CitizenProjectRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueCitizenProjectValidator extends ConstraintValidator
{
    private $repository;

    public function __construct(CitizenProjectRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueCitizenProject) {
            throw new UnexpectedTypeException($constraint, UniqueCitizenProject::class);
        }

        if (!$value instanceof CitizenProjectCommand) {
            throw new UnexpectedTypeException($value, CitizenProjectCommand::class);
        }

        if (!$value->name) {
            return;
        }

        $found = $this->repository->findOneByName($value->name);
        if (!$found instanceof CitizenProject) {
            return;
        }

        $CitizenProject = $value->getCitizenProject();

        if ($CitizenProject instanceof CitizenProject && $CitizenProject->equals($found)) {
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
