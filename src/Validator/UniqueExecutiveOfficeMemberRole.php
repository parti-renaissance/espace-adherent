<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueExecutiveOfficeMemberRole extends Constraint
{
    public $uniqueExecutiveOfficerMessage = 'executive_office_member.executive_officer.unique';
    public $uniqueRoleMessage = 'executive_office_member.role.unique';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
