<?php

namespace AppBundle\Validator;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
        if (!$constraint instanceof CityAssociatedToPostalCode) {
            throw new UnexpectedTypeException($constraint, CityAssociatedToPostalCode::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $postalCode = $this->propertyAccessor->getValue($value, $constraint->postalCodeField);
        $city = $this->propertyAccessor->getValue($value, $constraint->cityField);

        if (!$postalCode || !$city) {
            return;
        }

        list($zipCode) = explode('-', $city);

        if ($zipCode !== $postalCode) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->errorPath)
                ->setParameter('{{ postal_code }}', $postalCode)
                ->setParameter('{{ city }}', $city)
                ->addViolation()
            ;
        }
    }
}
