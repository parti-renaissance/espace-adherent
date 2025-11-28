<?php

declare(strict_types=1);

namespace App\Validator\Jecoute;

use App\Entity\Adherent;
use App\Entity\Jecoute\Survey;
use App\Geo\ManagedZoneProvider;
use App\Jecoute\SurveyTypeEnum;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class SurveyScopeTargetValidator extends ConstraintValidator
{
    private Security $security;
    private ScopeGeneratorResolver $scopeGeneratorResolver;
    private ManagedZoneProvider $managedZoneProvider;

    public function __construct(
        Security $security,
        ScopeGeneratorResolver $scopeGeneratorResolver,
        ManagedZoneProvider $managedZoneProvider,
    ) {
        $this->managedZoneProvider = $managedZoneProvider;
        $this->security = $security;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    public function validate($value, Constraint $constraint): void
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

        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return;
        }

        $scopeCode = $scope->getMainCode();

        if ($scope->isNational()) {
            if (!$value->isNational()) {
                $this->context->buildViolation($constraint->message)
                    ->setParameters([
                        '{{ scope }}' => $scopeCode,
                        '{{ type }}' => SurveyTypeEnum::LOCAL,
                    ])
                    ->addViolation()
                ;
            }
        } else {
            if ($value->isNational()) {
                $this->context->buildViolation($constraint->message)
                    ->setParameters([
                        '{{ scope }}' => $scopeCode,
                        '{{ type }}' => SurveyTypeEnum::NATIONAL,
                    ])
                    ->addViolation()
                ;
            } elseif ($value->getZone() && !$this->managedZoneProvider->zoneBelongsToSomeZones($value->getZone(), $scope->getZones())) {
                $this->context->buildViolation($constraint->invalidManagedZone)
                    ->atPath('zone')
                    ->addViolation()
                ;
            }
        }
    }
}
