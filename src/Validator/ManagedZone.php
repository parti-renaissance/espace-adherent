<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 */
class ManagedZone extends Constraint
{
    public $message = "Oups, vous n'avez pas accès à cette zone !";

    /**
     * @var string
     */
    public $spaceType;
    public $zoneGetMethodName = 'getZone';
    public $path;

    public function __construct($options = null)
    {
        if (null !== $options && !\is_array($options)) {
            $options = [
                'spaceType' => $options,
            ];
        }

        parent::__construct($options);
    }

    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
