<?php

namespace App\Security\Voter\Team;

use App\Entity\Adherent;
use App\Entity\Team\Team;
use App\Geo\ManagedZoneProvider;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\Security\Core\Security;

class EditTeamVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_TEAM';

    private Security $security;
    private ScopeGeneratorResolver $scopeGeneratorResolver;
    private ManagedZoneProvider $managedZoneProvider;

    public function __construct(
        Security $security,
        ScopeGeneratorResolver $scopeGeneratorResolver,
        ManagedZoneProvider $managedZoneProvider
    ) {
        $this->security = $security;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
        $this->managedZoneProvider = $managedZoneProvider;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scopeGenerator = $this->scopeGeneratorResolver->resolve();

        if (null === $scopeGenerator) {
            return false;
        }

        if (\in_array($scopeGenerator->getCode(), ScopeEnum::NATIONAL_SCOPES, true)) {
            return null === $subject->getZone();
        }

        if (null === $subject->getZone()) {
            return false;
        }

        return $this->managedZoneProvider->isManagerOfZone(
            $this->security->getUser(),
            $scopeGenerator->getCode(),
            $subject->getZone()
        );
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Team;
    }
}
