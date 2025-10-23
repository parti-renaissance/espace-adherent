<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;

class PublicationVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_PUBLICATION';
    public const PERMISSION_ITEM = 'CAN_EDIT_PUBLICATION_ITEM';

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    /** @param AdherentMessage $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return false;
        }

        if (!$scope->containsFeatures([FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS])) {
            return false;
        }

        $authorId = $teamOwnerId = $isSent = null;

        if ($subject instanceof AdherentMessage) {
            $authorId = $subject->getAuthor()?->getId();
            $teamOwnerId = $subject->teamOwner?->getId();
            $isSent = $subject->isSent();
        } elseif (\is_array($subject)) {
            $isSent = true;
            $authorId = $subject['author_id'] ?? null;
            $teamOwnerId = $subject['team_owner_id'] ?? null;
        }

        return $authorId === $adherent->getId() || (false === $isSent && $teamOwnerId === $scope->getMainUser()?->getId());
    }

    protected function supports(string $attribute, $subject): bool
    {
        return
            (self::PERMISSION === $attribute && $subject instanceof AdherentMessage)
            || (self::PERMISSION_ITEM === $attribute && \is_array($subject));
    }
}
