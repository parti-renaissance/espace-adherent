<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueExecutiveOfficer extends Constraint
{
    public $message = 'executive_office_member.executive_officer.unique';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
