<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Defines whether or not an Address is geocodable.
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class GeocodableAddress extends Constraint
{
    const INVALID_ERROR = 'db20d91f-9b70-4747-a75d-29ae0dfacf70';

    public $message = 'common.address.not_geocodable';
    public $service = 'app.validator.geocodable_address';

    public function validatedBy()
    {
        return $this->service;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
