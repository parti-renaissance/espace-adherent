<?php

namespace App\Security\Voter\Survey;

use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Geo\ManagedZoneProvider;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\Security\Core\Security;

class ReadSurveyVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_READ_SURVEY';

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
            return $subject->isNational();
        }

        if (ScopeEnum::REFERENT === $scopeGenerator->getCode()) {
            if ($subject instanceof NationalSurvey) {
                return true;
            }

            if ($subject instanceof LocalSurvey) {
                return $this->managedZoneProvider->isManagerOfZone(
                    $this->security->getUser(),
                    $scopeGenerator->getCode(),
                    $subject->getZone()
                );
            }
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Survey;
    }
}
