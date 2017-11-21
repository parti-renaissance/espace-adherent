<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the Unique CitizenProject validator.
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueCitizenProject extends Constraint
{
    public $errorPath = 'name';
    public $message = 'citizen_project.canonical_name.not_unique';
    public $service = 'app.validator.unique_citizen_project';

    public function validatedBy()
    {
        return $this->service;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
