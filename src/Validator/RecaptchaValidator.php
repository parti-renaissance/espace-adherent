<?php

namespace App\Validator;

use App\Recaptcha\RecaptchaApiClientInterface;
use App\Recaptcha\RecaptchaChallengeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RecaptchaValidator extends ConstraintValidator
{
    private RecaptchaApiClientInterface $recaptchaApiClient;

    public function __construct(RecaptchaApiClientInterface $recaptchaApiClient)
    {
        $this->recaptchaApiClient = $recaptchaApiClient;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Recaptcha) {
            throw new UnexpectedTypeException($constraint, Recaptcha::class);
        }

        if (!$value instanceof RecaptchaChallengeInterface) {
            throw new UnexpectedValueException($value, RecaptchaChallengeInterface::class);
        }

        $recaptchaAnswer = $value->getRecaptcha();

        if (empty($recaptchaAnswer)) {
            $this
                ->context
                ->buildViolation($constraint->emptyMessage)
                ->atPath('recaptcha')
                ->addViolation()
            ;

            return;
        }

        if (!$this->recaptchaApiClient->verify($recaptchaAnswer, $value->getRecaptchaSiteKey())) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath('recaptcha')
                ->addViolation()
            ;
        }
    }
}
