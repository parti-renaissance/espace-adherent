<?php

namespace AppBundle\Validator;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class CityAssociatedToPostalCodeValidator extends ConstraintValidator
{
    private $propertyAccessor;

    public function __construct(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || !$constraint instanceof CityAssociatedToPostalCode) {
            return;
        }

        $postalCode = $this->propertyAccessor->getValue($value, $constraint->postalCodeField);
        $city = $this->propertyAccessor->getValue($value, $constraint->cityField);

        if (!$postalCode || !$city) {
            return;
        }

        $parts = explode('-', $city);

        if ($parts[0] !== $postalCode) {
            $this->context->addViolation($constraint->message, [
                '{{ postal_code }}' => $postalCode,
                '{{ city }}' => $city,
            ]);
        }
    }
}
