<?php

namespace App\Validator;

use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

abstract class AbstractZonesInScopeZonesValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ZoneRepository $zoneRepository,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
    }

    protected function validateZones(array $zones, Constraint $constraint): void
    {
        $scope = $this->scopeGeneratorResolver->generate();
        if (!$scope || $scope->isNational() || !($managedZones = $scope->getZones())) {
            return;
        }

        if (!$this->zoneRepository->isInZones($zones, $managedZones)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
