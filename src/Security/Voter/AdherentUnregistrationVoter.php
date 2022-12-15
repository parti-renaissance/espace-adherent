<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\AdherentMandate\AdherentMandateRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdherentUnregistrationVoter extends Voter
{
    public const PERMISSION_UNREGISTER = 'UNREGISTER';

    public function __construct(private readonly AdherentMandateRepository $adherentMandateRepository)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION_UNREGISTER === $attribute && $subject instanceof Adherent;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        /** @var Adherent $subject */
        if ($subject->isUser()) {
            return true;
        }

        if ($subject->getMemberships()->getCommitteeCandidacyMembership(true)) {
            return false;
        }

        if (($coTerrMembership = $subject->getTerritorialCouncilMembership()) && $coTerrMembership->getActiveCandidacy()) {
            return false;
        }

        if ($this->adherentMandateRepository->hasActiveMandate($subject)) {
            return false;
        }

        return $subject->isBasicAdherent();
    }
}
