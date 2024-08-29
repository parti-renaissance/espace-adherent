<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AddressInManagedZones extends Constraint
{
    public $message = 'committee.address.not_in_zone';

    public function __construct(public readonly string $spaceType, $options = null, ?array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
