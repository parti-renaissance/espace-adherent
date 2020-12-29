<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class CommitteeAddress extends Constraint
{
    public $errorPath = 'address';
    public $notUniqueAddressMessage = 'committee.address.not_unique';
    public $notInZoneMessage = 'committee.address.not_in_zone';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
