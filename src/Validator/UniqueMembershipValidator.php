<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Membership\MembershipInterface;
use App\Repository\AdherentChangeEmailTokenRepository;
use App\Repository\AdherentRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueMembershipValidator extends ConstraintValidator
{
    private $adherentRepository;
    private $tokenStorage;
    private $changeEmailTokenRepository;

    public function __construct(
        AdherentRepository $adherentRepository,
        AdherentChangeEmailTokenRepository $changeEmailTokenRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->changeEmailTokenRepository = $changeEmailTokenRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function validate($member, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueMembership) {
            throw new UnexpectedTypeException($constraint, UniqueMembership::class);
        }

        if ($member instanceof MembershipInterface) {
            $email = $member->getEmailAddress();
        } elseif (\is_string($member)) {
            $email = $member;
        } else {
            throw new UnexpectedTypeException($member, MembershipInterface::class);
        }

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
                ->addViolation()
            ;
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
        if ($adherent = $this->adherentRepository->findOneByEmail($emailAddress)) {
            return $adherent;
        }

        if ($token = $this->changeEmailTokenRepository->findLastUnusedByEmail($emailAddress)) {
            return $this->adherentRepository->findOneByUuid($token->getAdherentUuid());
        }

        return $this->adherentRepository->findByUuid(Adherent::createUuid($emailAddress));
    }
}
