<?php

namespace AppBundle\Validator;

use AppBundle\Entity\Adherent;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Repository\AdherentRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueMembershipValidator extends ConstraintValidator
{
    private $repository;

    private $tokenStorage;

    public function __construct(AdherentRepository $repository, TokenStorageInterface $tokenStorage)
    {
        $this->repository = $repository;
        $this->tokenStorage = $tokenStorage;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueMembership) {
            throw new UnexpectedTypeException($constraint, UniqueMembership::class);
        }

        if (!$value instanceof MembershipRequest && !$value instanceof Adherent) {
            throw new UnexpectedTypeException($value, MembershipRequest::class);
        }

        $adherent = $this->repository->findByEmail($value->getEmailAddress());
        if (!$adherent) {
            $adherent = $this->repository->findByUuid(Adherent::createUuid($value->getEmailAddress()));
        }

        $connectedUser = true;
        if (null === $token = $this->tokenStorage->getToken()) {
            $connectedUser = false;
        }

        if (!is_object($user = $token->getUser())) {
            $connectedUser = false;
        }

        if ($adherent instanceof Adherent && (!$connectedUser || ($connectedUser && $adherent->getId() !== $user->getId()))) {
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
