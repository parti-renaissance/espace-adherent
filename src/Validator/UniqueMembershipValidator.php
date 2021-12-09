<?php

namespace App\Validator;

use App\Entity\Adherent;
use App\Membership\MembershipRequest\MembershipInterface;
use App\Repository\AdherentChangeEmailTokenRepository;
use App\Repository\AdherentRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

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
            throw new UnexpectedValueException($member, MembershipInterface::class);
        }

        // Chosen email address is not already taken by someone else
        if (!$email || !$adherentUuid = $this->findAdherentIdentifiers($email)) {
            return;
        }

        if ($member instanceof Adherent && $member->getUuid()->equals($adherentUuid)) {
            return;
        }

        // 1. User is not authenticated yet and wants to register with someone else email address.
        // 2. User is authenticated and wants to change his\her email address for someone else email address.
        $user = $this->getAuthenticatedUser();
        if (!$user || !$user->getUuid()->equals($adherentUuid)) {
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

    private function findAdherentIdentifiers(string $emailAddress): ?UuidInterface
    {
        if ($identifiers = $this->adherentRepository->findIdentifiersByEmail($emailAddress)) {
            return $identifiers['uuid'];
        }

        if ($token = $this->changeEmailTokenRepository->findLastUnusedByEmail($emailAddress)) {
            return ($adherent = $this->adherentRepository->findOneByUuid($token->getAdherentUuid())) ? $adherent->getUuid() : null;
        }

        return ($adherent = $this->adherentRepository->findByUuid(Adherent::createUuid($emailAddress))) ?
            $adherent->getUuid() : null;
    }
}
