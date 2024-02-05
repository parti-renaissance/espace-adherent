<?php

namespace App\Validator\OpenAI;

use App\OpenAI\Client\ClientInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidAssistantIdValidator extends ConstraintValidator
{
    public function __construct(private readonly ClientInterface $client)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidAssistantId) {
            throw new UnexpectedTypeException($constraint, ValidAssistantId::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!$this->client->hasAssistant($value)) {
            $this->context
                ->buildViolation($constraint->errorMessage)
                ->setParameter('{{ value }}', $value)
                ->addViolation()
            ;
        }
    }
}
