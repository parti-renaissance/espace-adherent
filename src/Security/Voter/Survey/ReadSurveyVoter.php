<?php

namespace App\Security\Voter\Survey;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Geo\ManagedZoneProvider;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeEnum;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\RequestStack;

class ReadSurveyVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_READ_SURVEY';

    private ManagedZoneProvider $managedZoneProvider;
    private AuthorizationChecker $authorizationChecker;
    private RequestStack $requestStack;

    public function __construct(
        ManagedZoneProvider $managedZoneProvider,
        AuthorizationChecker $authorizationChecker,
        RequestStack $requestStack
    ) {
        $this->managedZoneProvider = $managedZoneProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->requestStack = $requestStack;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scopeGenerator = $this->authorizationChecker->getScopeGenerator(
            $this->requestStack->getMasterRequest(),
            $adherent
        );

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
                    $adherent,
                    AdherentSpaceEnum::SCOPES[$scopeGenerator->getCode()],
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
