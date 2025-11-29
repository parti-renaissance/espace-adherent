<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueInCollection extends Constraint
{
    public $message = 'constraint.unique_in_collection';
    public $propertyPath;

    public function __construct(
        ?string $propertyPath = null,
        ?string $message = null,
        $options = null,
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        $this->propertyPath = $propertyPath ?? $this->propertyPath;
        $this->message = $message ?? $this->message;
    }
}
