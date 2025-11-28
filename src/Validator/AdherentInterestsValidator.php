<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class AdherentInterestsValidator extends ConstraintValidator
{
    /**
     * @var array
     */
    private $adherentInterests;

    public function __construct(array $adherentInterests)
    {
        $this->adherentInterests = $adherentInterests;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AdherentInterests) {
            throw new UnexpectedTypeException($constraint, AdherentInterests::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        if (array_diff($value, array_keys($this->adherentInterests))) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
