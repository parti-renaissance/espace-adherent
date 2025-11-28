<?php

declare(strict_types=1);

namespace App\Validator;

use App\Recaptcha\RecaptchaApiClientInterface;
use App\Recaptcha\RecaptchaChallengeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RecaptchaValidator extends ConstraintValidator
{
    /** @var RecaptchaApiClientInterface[]|iterable */
    private iterable $apiClients;

    public function __construct(iterable $apiClients)
    {
        $this->apiClients = $apiClients;
    }

    public function validate($value, Constraint $constraint): void
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

        if (!$this->getClient($constraint->api)->verify($recaptchaAnswer, $value->getRecaptchaSiteKey())) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath('recaptcha')
                ->addViolation()
            ;
        }
    }

    private function getClient(string $name): RecaptchaApiClientInterface
    {
        foreach ($this->apiClients as $apiClient) {
            if ($apiClient->supports($name)) {
                return $apiClient;
            }
        }

        throw new \InvalidArgumentException(\sprintf('No captcha client found with name "%s".', $name));
    }
}
