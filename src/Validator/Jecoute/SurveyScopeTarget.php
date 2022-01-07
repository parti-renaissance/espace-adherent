<?php

namespace App\Validator\Jecoute;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SurveyScopeTarget extends Constraint
{
    public string $message = 'survey.with_wrong_scope';
    public string $invalidManagedZone = 'Oups, vous n\'avez pas accès à cette zone !';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
