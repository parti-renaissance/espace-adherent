<?php

namespace App\Security\Voter\Event;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\FeatureEnum;
use App\Scope\GeneralScopeGenerator;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\AbstractAdherentVoter;

class CanManageEventVoter extends AbstractAdherentVoter
{
    public const CAN_MANAGE_EVENT = 'CAN_MANAGE_EVENT';
    public const CAN_MANAGE_EVENT_ITEM = 'CAN_MANAGE_EVENT_ITEM';

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly GeneralScopeGenerator $generalScopeGenerator,
    ) {
    }

    /** @param BaseEvent $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (self::CAN_MANAGE_EVENT === $attribute) {
            return $this->canManageEvent($subject);
        }

        if (self::CAN_MANAGE_EVENT_ITEM === $attribute) {
            return $this->canManageEventItem($adherent, $subject);
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return (self::CAN_MANAGE_EVENT === $attribute && $subject instanceof BaseEvent)
            || (self::CAN_MANAGE_EVENT_ITEM === $attribute && \is_array($subject));
    }

    private function canManageEvent(BaseEvent $subject): bool
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return false;
        }

        if (!$scope->hasFeature(FeatureEnum::EVENTS)) {
            return false;
        }

        return $scope->getMainUser() === $subject->getAuthor();
    }

    private function canManageEventItem(Adherent $adherent, array $event): bool
    {
        if (empty($event['scope'])) {
            return false;
        }

        try {
            $scope = $this->generalScopeGenerator->getGenerator($event['scope'], $adherent)->generate($adherent);
        } catch (ScopeExceptionInterface $e) {
            return false;
        }

        if (!$scope->hasFeature(FeatureEnum::EVENTS)) {
            return false;
        }

        return $scope->getMainUser()->getUuidAsString() === $event['author_uuid'];
    }
}
