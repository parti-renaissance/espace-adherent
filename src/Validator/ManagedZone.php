<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ManagedZone extends Constraint
{
    public $message = "Oups, vous n'avez pas accès à cette zone !";

    public $zoneGetMethodName = 'getZone';
    public $path;

    public function __construct(?string $path = null, ?string $message = null, $options = null)
    {
        $this->path = $path;

        if (null !== $message) {
            $this->message = $message;
        }

        parent::__construct($options);
    }

    public function getTargets(): string|array
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
