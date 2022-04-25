<?php

namespace App\Validator;

use App\Address\AddressInterface;
use App\Geo\ZoneMatcher;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AddressInScopeZonesValidator extends ConstraintValidator
{
    private ZoneMatcher $zoneMatcher;
    private ZoneRepository $zoneRepository;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(
        ZoneMatcher $zoneMatcher,
        ZoneRepository $zoneRepository,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
        $this->zoneMatcher = $zoneMatcher;
        $this->zoneRepository = $zoneRepository;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$constraint instanceof AddressInScopeZones) {
            throw new UnexpectedTypeException($constraint, AddressInScopeZones::class);
        }

        if (!$value instanceof AddressInterface) {
            throw new UnexpectedValueException($value, AddressInterface::class);
        }

        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope || $scope->isNational() || !($managedZones = $scope->getZones())) {
            return;
        }

        $zones = $this->zoneMatcher->match($value);

        if (!$this->zoneRepository->isInZones($zones, $managedZones)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
