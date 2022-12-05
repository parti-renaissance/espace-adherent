<?php

namespace App\Validator\Assessor;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AssessorDepartment extends Constraint
{
    public string $invalidAssessorDepartmentCity = 'Cette commune ne fait pas partie des communes de votre département de résidence';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
