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
    public $uniquePresidentOrExecutiveOfficerMessage = 'executive_office_member.president_or_executive_officer';
    public $uniquePresidentOrDeputyGeneralDelegate = 'executive_office_member.president_or_deputy_general_delegate';
    public $uniqueExecutiveOfficerOrDeputyGeneralDelegate = 'executive_office_member.executive_officer_or_deputy_general_delegate';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
