<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class MyTeamMemberScopeFeatures extends Constraint
{
    public $message = 'Vous pouvez déléguer que les accès que vous possédez.';
}
