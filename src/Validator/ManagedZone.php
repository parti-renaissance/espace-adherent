<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ManagedZone extends Constraint
{
    public $message = "Oups, vous n'avez pas accès à cette zone !";

    /**
     * @var string
     */
    public $spaceType;
    public $zoneGetMethodName = 'getZone';
    public $path;

    public function __construct(?string $path = null, ?string $message = null, $options = null)
    {
        if (null !== $options && !\is_array($options)) {
            $options = [
                'spaceType' => $options,
            ];
        }
        $this->path = $path;
        $this->message = $message;

        parent::__construct($options);
    }

    public function getTargets(): string|array
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
