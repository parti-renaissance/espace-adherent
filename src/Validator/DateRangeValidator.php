<?php

namespace App\Validator;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DateRangeValidator extends ConstraintValidator
{
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DateRange) {
            throw new UnexpectedTypeException($constraint, DateRange::class);
        }

        if (null === $value) {
            return;
        }

        foreach ([$constraint->startDateField, $constraint->endDateField] as $field) {
            if (!$this->propertyAccessor->isReadable($value, $field)) {
                throw new ConstraintDefinitionException(sprintf('The field "%s" is not readable', $field));
            }
        }

        $startDate = $this->propertyAccessor->getValue($value, $constraint->startDateField);
        $endDate = $this->propertyAccessor->getValue($value, $constraint->endDateField);

        if (!$startDate instanceof \DateTime) {
            throw new ConstraintDefinitionException('The start date field should be of type DateTime');
        }

        if (!$endDate instanceof \DateTimeInterface) {
            throw new ConstraintDefinitionException('The start date field should be of type DateTime');
        }

        $interval = \DateInterval::createFromDateString($constraint->interval);

        if (($maxEndDate = (clone $startDate)->add($interval)) < $endDate) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->endDateField)
                ->setParameter('{{ limit }}', $this->formatValue($maxEndDate, self::PRETTY_DATE))
                ->addViolation()
            ;
        }
    }
}
