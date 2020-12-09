<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class ManagedZone extends Constraint
{
    public $message = "Oups, vous n'avez pas accès à cette zone !";

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
}
