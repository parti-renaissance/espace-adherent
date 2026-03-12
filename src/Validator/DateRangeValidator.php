<?php

declare(strict_types=1);

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

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DateRange) {
            throw new UnexpectedTypeException($constraint, DateRange::class);
        }

        if (null === $value) {
            return;
        }

        foreach ([$constraint->startDateField, $constraint->endDateField] as $field) {
            if (!$this->propertyAccessor->isReadable($value, $field)) {
                throw new ConstraintDefinitionException(\sprintf('The field "%s" is not readable', $field));
            }
        }

        $startDate = $this->propertyAccessor->getValue($value, $constraint->startDateField);
        $endDate = $this->propertyAccessor->getValue($value, $constraint->endDateField);

        if (null === $startDate || null === $endDate) {
            return;
        }

        if (!$startDate instanceof \DateTimeInterface) {
            throw new ConstraintDefinitionException('The start date field should be of type DateTimeInterface');
        }

        if (!$endDate instanceof \DateTimeInterface) {
            throw new ConstraintDefinitionException('The end date field should be of type DateTimeInterface');
        }

        $intervals = explode('|', $constraint->interval, 2);

        $startDateImmutable = $startDate instanceof \DateTimeImmutable
            ? $startDate
            : \DateTimeImmutable::createFromMutable($startDate);
        $maxEndDate = $startDateImmutable->add(\DateInterval::createFromDateString($intervals[0]));

        if (1 === \count($intervals)) {
            if ($maxEndDate < $endDate) {
                $this
                    ->context
                    ->buildViolation($constraint->messageDate)
                    ->atPath($constraint->endDateField)
                    ->setParameter('{{ limit }}', $this->formatValue($maxEndDate, self::PRETTY_DATE))
                    ->addViolation()
                ;
            }

            return;
        }

        $maxEndDateB = $startDateImmutable->add(\DateInterval::createFromDateString($intervals[1]));

        if ($endDate < $maxEndDate || $endDate > $maxEndDateB) {
            $this
                ->context
                ->buildViolation($constraint->messageInterval)
                ->atPath($constraint->endDateField)
                ->setParameter('{{ dateMin }}', $this->formatValue($maxEndDate, self::PRETTY_DATE))
                ->setParameter('{{ dateMax }}', $this->formatValue($maxEndDateB, self::PRETTY_DATE))
                ->addViolation()
            ;
        }
    }
}
