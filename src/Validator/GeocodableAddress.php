<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class GeocodableAddress extends Constraint
{
    public const INVALID_ERROR = 'db20d91f-9b70-4747-a75d-29ae0dfacf70';

    public $message = 'common.address.not_geocodable';
    public $service = GeocodableAddressValidator::class;

    public function __construct(?string $message = null, $options = null, ?array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return $this->service;
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
