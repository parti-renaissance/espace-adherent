<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Geo\Zone;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

#[\Attribute]
class ZoneType extends Constraint
{
    public string $message = 'Le type de la zone est invalide';

    public array $types = [];

    public function __construct(array $types, $options = null, ?array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);

        $this->types = $types;

        if (!array_intersect($this->types, Zone::TYPES)) {
            throw new InvalidArgumentException('Invalid types');
        }
    }
}
