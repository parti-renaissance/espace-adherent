<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueExecutiveOfficeMemberRole extends Constraint
{
    public $uniquePresidentMessage = 'executive_office_member.president.unique';
    public $uniqueExecutiveOfficerMessage = 'executive_office_member.executive_officer.unique';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
