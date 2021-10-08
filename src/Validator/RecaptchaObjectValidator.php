<?php

namespace App\Validator;

use App\Entity\RecaptchaObjectInterface;
use App\Recaptcha\RecaptchaApiClient;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RecaptchaObjectValidator extends ConstraintValidator
{
    private $httpClient;

    public function __construct(RecaptchaApiClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof RecaptchaObject) {
            throw new UnexpectedTypeException($constraint, RecaptchaObject::class);
        }

        if (!$value instanceof RecaptchaObjectInterface) {
            return;
        }

        if (!$value->isRequiredRecaptcha()) {
            return;
        }

        if (null === $value->getRecaptcha() || '' === $value->getRecaptcha()) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }

        $reCaptchaAnswer = (string) $value->getRecaptcha();
        if (!$this->httpClient->verify($reCaptchaAnswer)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
