<?php

namespace App\Validator;

use App\Recaptcha\RecaptchaApiClient;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class Recaptcha extends Constraint
{
    public string $emptyMessage = 'common.recaptcha.empty_message';
    public string $message = 'common.recaptcha.invalid_message';
    public string $api = RecaptchaApiClient::NAME;

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
