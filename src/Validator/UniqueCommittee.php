<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the Unique Committee validator.
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueCommittee extends Constraint
{
    public $errorPathName = 'name';
    public $errorPathAddress = 'address';
    public $messageName = 'committee.canonical_name.not_unique';
    public $messageAddress = 'committee.address.not_unique';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
