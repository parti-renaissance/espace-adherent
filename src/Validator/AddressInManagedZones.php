<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class AddressInManagedZones extends Constraint
{
    public $message = 'committee.address.not_in_zone';

    /**
     * @var string
     */
    public $spaceType;

    public function __construct($options = null)
    {
        if (null !== $options && !\is_array($options)) {
            $options = [
                'spaceType' => $options,
            ];
        }

        parent::__construct($options);
    }

    /**
     * @return string[]
     */
    public function getRequiredOptions(): array
    {
        return [
            'spaceType',
        ];
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
