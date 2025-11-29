<?php

declare(strict_types=1);

namespace App\Validator\Jecoute;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class SurveyScopeTarget extends Constraint
{
    public string $message = 'survey.with_wrong_scope';
    public string $invalidManagedZone = 'Oups, vous n\'avez pas accès à cette zone !';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
