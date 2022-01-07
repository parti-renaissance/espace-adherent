<?php

namespace App\Validator\Jecoute;

use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Geo\ManagedZoneProvider;
use App\Jecoute\SurveyTypeEnum;
use App\Scope\AuthorizationChecker;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\ScopeEnum;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class SurveyScopeTargetValidator extends ConstraintValidator
{
    private Security $security;
    private RequestStack $requestStack;
    private AuthorizationChecker $authorizationChecker;
    private ManagedZoneProvider $managedZoneProvider;

    public function __construct(
        Security $security,
        RequestStack $requestStack,
        AuthorizationChecker $authorizationChecker,
        ManagedZoneProvider $managedZoneProvider
    ) {
        $this->managedZoneProvider = $managedZoneProvider;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof SurveyScopeTarget) {
            throw new UnexpectedTypeException($constraint, SurveyScopeTarget::class);
        }

        if (null === $value) {
            return;
        }

        if (!is_a($value, Survey::class, true)) {
            throw new UnexpectedValueException($value, Survey::class);
        }

        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof Adherent) {
            return;
        }

        if (!$this->authorizationChecker->getScope($this->requestStack->getMasterRequest())) {
            return;
        }

        try {
            $scopeGenerator = $this->authorizationChecker->getScopeGenerator($this->requestStack->getMasterRequest(), $currentUser);
        } catch (ScopeExceptionInterface $e) {
            return;
        }

        if (\in_array($scopeGenerator->getCode(), ScopeEnum::NATIONAL_SCOPES, true)
            && $value instanceof LocalSurvey
        ) {
            $this->context->buildViolation($constraint->message)
                ->setParameters([
                    '{{ scope }}' => $scopeGenerator->getCode(),
                    '{{ type }}' => SurveyTypeEnum::LOCAL,
                ])
                ->addViolation()
            ;
        }

        if (ScopeEnum::REFERENT === $scopeGenerator->getCode()) {
            if ($value instanceof NationalSurvey) {
                $this->context->buildViolation($constraint->message)
                    ->setParameters([
                        '{{ scope }}' => $scopeGenerator->getCode(),
                        '{{ type }}' => SurveyTypeEnum::NATIONAL,
                    ])
                    ->addViolation()
                ;
            }

            if ($value instanceof LocalSurvey
                && $value->getZone()
                && !$this->managedZoneProvider->isManagerOfZone($currentUser, $scopeGenerator->getCode(), $value->getZone())
            ) {
                $this->context->buildViolation($constraint->invalidManagedZone)
                    ->atPath('zone')
                    ->addViolation()
                ;
            }
        }
    }
}
