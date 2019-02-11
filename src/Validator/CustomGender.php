<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class CustomGender extends Constraint
{
    public $messageNotBlank = 'common.gender.not_blank';
    public $messageInvalidChoice = 'common.gender.invalid_choice';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
