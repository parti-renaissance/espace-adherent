<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class AdherentForCommitteeMandateReplacement extends Constraint
{
    public $errorPath;

    public function getRequiredOptions()
    {
        return ['errorPath'];
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
