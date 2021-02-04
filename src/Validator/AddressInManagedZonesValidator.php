<?php

namespace App\Validator;

use App\Address\Address as Address;
use App\Geo\ManagedZoneProvider;
use App\Geo\ZoneMatcher;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AddressInManagedZonesValidator extends AbstractInManagedZoneValidator
{
    private $zoneMatcher;

    public function __construct(
        Security $security,
        SessionInterface $session,
        ZoneMatcher $zoneMatcher,
        ManagedZoneProvider $managedZoneProvider
    ) {
        parent::__construct($managedZoneProvider, $security, $session);

        $this->zoneMatcher = $zoneMatcher;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AddressInManagedZones) {
            throw new UnexpectedTypeException($constraint, AddressInManagedZones::class);
        }

        if (!$value instanceof Address) {
            throw new UnexpectedValueException($value, Address::class);
        }

        $this->validateZones(
            $this->zoneMatcher->match($value),
            $constraint
        );
    }
}
