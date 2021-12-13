<?php

namespace App\Security\Voter\Team;

use App\Entity\Adherent;
use App\Entity\Team\Team;
use App\Geo\ManagedZoneProvider;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeEnum;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class EditTeamVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_TEAM';

    private AuthorizationChecker $authorizationChecker;
    private RequestStack $requestStack;
    private Security $security;
    private ManagedZoneProvider $managedZoneProvider;

    public function __construct(
        AuthorizationChecker $authorizationChecker,
        RequestStack $requestStack,
        Security $security,
        ManagedZoneProvider $managedZoneProvider
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->managedZoneProvider = $managedZoneProvider;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $adherent = $this->getAdherent();

        $scopeGenerator = $this->authorizationChecker->getScopeGenerator($this->getRequest(), $adherent);

        if (\in_array($scopeGenerator->getCode(), ScopeEnum::NATIONAL_SCOPES, true)) {
            return null === $subject->getZone();
        }

        if (null === $subject->getZone()) {
            return false;
        }

        return $this->managedZoneProvider->isManagerOfZone($adherent, $scopeGenerator->getCode(), $subject->getZone());
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Team;
    }

    private function getAdherent(): Adherent
    {
        return $this->security->getUser();
    }

    private function getRequest(): Request
    {
        return $this->requestStack->getMasterRequest();
    }
}
