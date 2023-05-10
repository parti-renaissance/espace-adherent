<?php

namespace App\Validator;

use App\Entity\AddressHolderInterface;
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

        if (!$value instanceof AddressHolderInterface) {
            throw new UnexpectedValueException($value, AddressHolderInterface::class);
        }

        if (!$address = $value->getPostAddress()) {
            return;
        }

        $zones = $this->zoneMatcher->match($address);

        $this->validateZones($zones, $constraint, 'postAddress');
    }
}
