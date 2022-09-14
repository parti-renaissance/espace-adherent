<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueRenaissanceNewsletter extends Constraint
{
    public string $message = 'newsletter.already_registered';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
