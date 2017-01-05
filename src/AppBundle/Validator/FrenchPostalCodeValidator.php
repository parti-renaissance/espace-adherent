<?php

namespace AppBundle\Validator;

use AppBundle\Intl\FranceCitiesBundle;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @Annotation
 */
class FrenchPostalCodeValidator extends ConstraintValidator
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

        if (0 === count(FranceCitiesBundle::getPostalCodeCities($value))) {
            $this->context->addViolation($constraint->message, ['{{ value }}' => $value]);
        }
    }
}
