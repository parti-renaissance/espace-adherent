<?php

namespace App\Validator;

use App\Recaptcha\RecaptchaApiClientInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RecaptchaValidator extends ConstraintValidator
{
    private $httpClient;

    public function __construct(RecaptchaApiClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Recaptcha) {
            throw new UnexpectedTypeException($constraint, Recaptcha::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $reCaptchaAnswer = (string) $value;
        if (!$this->httpClient->verify($reCaptchaAnswer)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
