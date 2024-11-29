<?php

namespace App\Validator;

use App\Address\Address;
use App\Geo\ManagedZoneProvider;
use App\Geo\ZoneMatcher;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AddressInManagedZonesValidator extends AbstractInManagedZoneValidator
{
    public function __construct(
        ManagedZoneProvider $managedZoneProvider,
        Security $security,
        private readonly ZoneMatcher $zoneMatcher,
    ) {
        parent::__construct($managedZoneProvider, $security);
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AddressInManagedZones) {
            throw new UnexpectedTypeException($constraint, AddressInManagedZones::class);
        }

        if (!$value instanceof Address) {
            throw new UnexpectedValueException($value, Address::class);
        }

        $this->validateZones($this->zoneMatcher->match($value), $constraint);
    }
}
