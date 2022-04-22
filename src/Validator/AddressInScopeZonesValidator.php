<?php

namespace App\Validator;

use App\Address\AddressInterface;
use App\Geo\ZoneMatcher;
use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\Geocoder;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AddressInScopeZonesValidator extends ConstraintValidator
{
    private Geocoder $geocoder;
    private ZoneMatcher $zoneMatcher;
    private ZoneRepository $zoneRepository;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(
        Geocoder $geocoder,
        ZoneMatcher $zoneMatcher,
        ZoneRepository $zoneRepository,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
        $this->zoneMatcher = $zoneMatcher;
        $this->zoneRepository = $zoneRepository;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
        $this->geocoder = $geocoder;
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

        try {
            $value->updateCoordinates($this->geocoder->geocode($value->getGeocodableAddress()));
        } catch (GeocodingException $e) {
            // do nothing when an exception arises
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
