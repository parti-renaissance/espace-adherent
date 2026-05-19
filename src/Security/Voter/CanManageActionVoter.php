<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Action\Action;
use App\Entity\Adherent;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\FeatureEnum;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;

class CanManageActionVoter extends AbstractAdherentVoter
{
    public const CAN_MANAGE_ACTION = 'CAN_MANAGE_ACTION';
    public const CAN_MANAGE_ACTION_ITEM = 'CAN_MANAGE_ACTION_ITEM';

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly GeneralScopeGenerator $generalScopeGenerator,
    ) {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (self::CAN_MANAGE_ACTION === $attribute) {
            return $this->canManageAction($adherent, $subject);
        }

        if (self::CAN_MANAGE_ACTION_ITEM === $attribute) {
            return $this->canManageActionItem($adherent, $subject);
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return (self::CAN_MANAGE_ACTION === $attribute && $subject instanceof Action)
            || (self::CAN_MANAGE_ACTION_ITEM === $attribute && \is_array($subject));
    }

    private function canManageAction(Adherent $adherent, Action $subject): bool
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return $this->canManageActionItem($adherent, [
                'author_uuid' => $subject->getAuthor()?->getUuidAsString(),
            ]);
        }

        if (!$scope->hasFeature(FeatureEnum::ACTIONS)) {
            return false;
        }

        return ($scope->getDelegator() ?? $adherent) === $subject->getAuthor();
    }

    private function canManageActionItem(Adherent $adherent, array $payload): bool
    {
        if (empty($payload['author_uuid'])) {
            return false;
        }

        try {
            $scopes = array_filter(
                $this->generalScopeGenerator->generateScopes($adherent),
                static fn (Scope $scope) => $scope->hasFeature(FeatureEnum::ACTIONS),
            );
        } catch (ScopeExceptionInterface) {
            return false;
        }

        if (empty($scopes)) {
            return false;
        }

        foreach ($scopes as $scope) {
            $actor = $scope->getDelegator() ?? $adherent;

            if ($actor->getUuidAsString() === $payload['author_uuid']) {
                return true;
            }
        }

        return false;
    }
}
