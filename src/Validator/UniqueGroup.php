<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the Unique Group validator.
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueGroup extends Constraint
{
    public $errorPath = 'name';
    public $message = 'group.canonical_name.not_unique';
    public $service = 'app.validator.unique_group';

    public function validatedBy()
    {
        return $this->service;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
