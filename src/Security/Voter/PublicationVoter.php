<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\FeatureEnum;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;

class PublicationVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_PUBLICATION';
    public const PERMISSION_ITEM = 'CAN_EDIT_PUBLICATION_ITEM';

    public function __construct(
        private readonly GeneralScopeGenerator $generalScopeGenerator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    /** @param AdherentMessage $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (self::PERMISSION_ITEM === $attribute) {
            try {
                $scopes = array_filter(
                    $this->generalScopeGenerator->generateScopes($adherent),
                    static fn (Scope $scope) => $scope->containsFeatures([FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS])
                );
            } catch (ScopeExceptionInterface $e) {
                return false;
            }

            if (empty($scopes)) {
                return false;
            }

            if ($adherent->getId() === ($subject['author_id'] ?? null)) {
                return true;
            }

            $teamOwnerId = $subject['team_owner_id'] ?? null;

            foreach ($scopes as $scope) {
                if ($scope->getMainUser()?->getId() === $teamOwnerId) {
                    return true;
                }
            }

            return false;
        }

        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return false;
        }

        if (!$scope->containsFeatures([FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS])) {
            return false;
        }

        return $subject->getAuthor() === $adherent || $subject->teamOwner === $scope->getMainUser();
    }

    protected function supports(string $attribute, $subject): bool
    {
        return
            (self::PERMISSION === $attribute && $subject instanceof AdherentMessage)
            || (self::PERMISSION_ITEM === $attribute && \is_array($subject));
    }
}
