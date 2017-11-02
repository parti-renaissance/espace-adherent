<?php

namespace AppBundle\Validator;

use AppBundle\Entity\Adherent;
use AppBundle\Membership\MembershipInterface;
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

    public function validate($member, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueMembership) {
            throw new UnexpectedTypeException($constraint, UniqueMembership::class);
        }

        if (!$member instanceof MembershipInterface) {
            throw new UnexpectedTypeException($member, MembershipInterface::class);
        }

        $email = $member->getEmailAddress();

        // Chosen email address is not already taken by someone else
        if (!$email || !$adherent = $this->findAdherent($email)) {
            return;
        }

        // 1. User is not authenticated yet and wants to register with someone else email address.
        // 2. User is authenticated and wants to change his\her email address for someone else email address.
        $user = $this->getAuthenticatedUser();
        if (!$user || !$user->equals($adherent)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ email }}', $email)
                ->atPath('emailAddress')
                ->addViolation();
        }
    }

    private function getAuthenticatedUser(): ?Adherent
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return null;
        }

        $user = $token->getUser();
        if (!$user instanceof Adherent) {
            return null;
        }

        return $user;
    }

    private function findAdherent(string $emailAddress): ?Adherent
    {
        if ($adherent = $this->repository->findByEmail($emailAddress)) {
            return $adherent;
        }

        return $this->repository->findByUuid(Adherent::createUuid($emailAddress));
    }
}
