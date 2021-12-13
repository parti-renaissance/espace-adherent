<?php

namespace App\Validator\Team;

use App\Entity\Adherent;
use App\Entity\Team\Team;
use App\Geo\ManagedZoneProvider;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TeamZoneValidator extends ConstraintValidator
{
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

    /**
     * @param Team                $value
     * @param TeamZone|Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TeamZone) {
            throw new UnexpectedTypeException($constraint, TeamZone::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Team) {
            throw new UnexpectedTypeException($value, Team::class);
        }

        $scopeGenerator = $this->authorizationChecker->getScopeGenerator($this->getRequest(), $this->getAdherent());

        if (\in_array($scopeGenerator->getCode(), ScopeEnum::NATIONAL_SCOPES, true)) {
            if (null !== $value->getZone()) {
                $this
                    ->context
                    ->buildViolation($constraint->nationalScopeWithZoneMessage)
                    ->atPath('zone')
                    ->addViolation()
                ;
            }

            return;
        }

        if (null === $value->getZone()) {
            $this
                ->context
                ->buildViolation($constraint->localScopeWithoutZoneMessage)
                ->atPath('zone')
                ->addViolation()
            ;

            return;
        }

        if (!$this->managedZoneProvider->isManagerOfZone($this->getAdherent(), $scopeGenerator->getCode(), $value->getZone())) {
            $this
                ->context
                ->buildViolation($constraint->localScopeWithUnmanagedZoneMessage)
                ->atPath('zone')
                ->addViolation()
            ;
        }
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
