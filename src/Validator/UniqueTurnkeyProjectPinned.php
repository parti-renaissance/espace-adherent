<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueTurnkeyProjectPinned extends Constraint
{
    public $message = 'turnkey_projects.pinned.unique';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
