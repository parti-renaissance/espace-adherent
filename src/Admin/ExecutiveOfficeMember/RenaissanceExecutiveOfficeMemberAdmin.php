<?php

namespace App\Admin\ExecutiveOfficeMember;

use App\Entity\Biography\ExecutiveOfficeMember;

class RenaissanceExecutiveOfficeMemberAdmin extends AbstractExecutiveOfficeMemberAdmin
{
    protected $baseRoutePattern = 'renaissance-burex';
    protected $baseRouteName = 'renaissance-burex';

    protected function createNewInstance(): object
    {
        /** @var ExecutiveOfficeMember $member */
        $member = parent::createNewInstance();
        $member->setForRenaissance(true);

        return $member;
    }

    protected function isForRenaissance(): bool
    {
        return true;
    }
}
