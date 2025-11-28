<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

abstract class AbstractZonesInScopeZonesValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ZoneRepository $zoneRepository,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    protected function validateZones(array $zones, Constraint $constraint, ?string $path = null): void
    {
        $scope = $this->scopeGeneratorResolver->generate();
        if (!$scope || $scope->isNational() || !($managedZones = $scope->getZones())) {
            return;
        }

        if (!$this->zoneRepository->isInZones($zones, $managedZones)) {
            $violationBuilder = $this->context->buildViolation($constraint->message);
            if ($path) {
                $violationBuilder->atPath($path);
            }
            $violationBuilder->addViolation();
        }
    }
}
