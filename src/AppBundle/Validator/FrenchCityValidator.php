<?php

namespace AppBundle\Validator;

use AppBundle\Intl\FranceCitiesBundle;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class FrenchCityValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $parts = explode('-', $value);

        if (2 !== count($parts) || !FranceCitiesBundle::getCity($parts[0], $parts[1])) {
            $this->context->addViolation($constraint->message, ['{{ value }}' => $value]);
        }
    }
}
