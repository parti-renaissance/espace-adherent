<?php

namespace App\Validator;

use App\Address\AddressInterface;
use App\Geo\ZoneMatcher;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AddressInScopeZonesValidator extends AbstractZonesInScopeZonesValidator
{
    public function __construct(
        private readonly ZoneMatcher $zoneMatcher,
        ZoneRepository $zoneRepository,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
        parent::__construct($zoneRepository, $scopeGeneratorResolver);
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

        $zones = $this->zoneMatcher->match($value);

        $this->validateZones($zones, $constraint);
    }
}
