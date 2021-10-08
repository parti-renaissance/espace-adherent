<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class RecaptchaObject extends Constraint
{
    public $message = 'common.recaptcha.invalid_message';
    public $service = RecaptchaObjectValidator::class;

    public function validatedBy()
    {
        return $this->service;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
