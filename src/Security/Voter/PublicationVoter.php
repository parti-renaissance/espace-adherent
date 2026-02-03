<?php

declare(strict_types=1);

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
            return $this->canManagePublicationItem($adherent, $subject);
        }

        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return $this->canManagePublicationItem($adherent, [
                'author_id' => $subject->getAuthor()?->getId(),
                'team_owner_id' => $subject->teamOwner?->getId(),
            ]);
        }

        if (!$scope->containsFeatures([FeatureEnum::MESSAGES, FeatureEnum::PUBLICATIONS])) {
            return false;
        }

        if ($scope->isNational()) {
            return true;
        }

        return $subject->getAuthor() === $adherent || $subject->teamOwner === $scope->getMainUser();
    }

    private function canManagePublicationItem(Adherent $adherent, array $publicationData): bool
    {
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

        if ($adherent->getId() === $publicationData['author_id']) {
            return true;
        }

        return array_any($scopes, static fn (Scope $scope) => $scope->isNational() || $scope->getMainUser()?->getId() === $publicationData['team_owner_id']);
    }

    protected function supports(string $attribute, $subject): bool
    {
        return
            (self::PERMISSION === $attribute && $subject instanceof AdherentMessage)
            || (self::PERMISSION_ITEM === $attribute && \is_array($subject));
    }
}
