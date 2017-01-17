<?php

namespace AppBundle\Validator;

use AppBundle\Entity\Adherent;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Repository\AdherentRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueMembershipValidator extends ConstraintValidator
{
    private $repository;

    public function __construct(AdherentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueMembership) {
            throw new UnexpectedTypeException($constraint, UniqueMembership::class);
        }

        if (!$value instanceof MembershipRequest) {
            throw new UnexpectedTypeException($value, MembershipRequest::class);
        }

        $adherent = $this->repository->findByEmail($value->getEmailAddress());

        if ($adherent instanceof Adherent) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ email }}', $value->getEmailAddress())
                ->atPath('emailAddress')
                ->addViolation()
            ;
        }
    }
}
