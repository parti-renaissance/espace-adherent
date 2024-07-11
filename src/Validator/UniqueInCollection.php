<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueInCollection extends Constraint
{
    public $message = 'constraint.unique_in_collection';
    public $propertyPath;

    public function __construct(?string $message = null, ?string $propertyPath = null, ?array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->message = $message ?? $this->message;
        $this->propertyPath = $propertyPath;
    }
}
