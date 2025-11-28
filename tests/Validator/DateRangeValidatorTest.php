<?php

declare(strict_types=1);

namespace Tests\App\Validator;

use App\Validator\DateRange;
use App\Validator\DateRangeValidator;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class DateRangeValidatorTest extends ConstraintValidatorTestCase
{
    public function testStartDateFieldIsNotReadableThrowsException(): void
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->validator->validate(new \stdClass(), new DateRange(
            'foo',
            'bar',
            '01010',
        ));
    }

    public function testInvalidStartDateThrowsException(): void
    {
        $this->expectException(ConstraintDefinitionException::class);
        $object = new \stdClass();

        $object->foo = true;
        $object->bar = 'hello world';

        $this->validator->validate($object, new DateRange(
            'foo',
            'bar',
            '01010',
        ));
    }

    public function testEndDateFieldIsNotReadableThrowsException(): void
    {
        $this->expectException(ConstraintDefinitionException::class);
        $object = new \stdClass();
        $object->foo = true;

        $this->validator->validate($object, new DateRange(
            'foo',
            'bar',
            '01010',
        ));
    }

    public function testInvalidEndDateThrowsException(): void
    {
        $this->expectException(ConstraintDefinitionException::class);
        $object = new \stdClass();

        $object->foo = new \DateTime();
        $object->bar = 'hello world';

        $this->validator->validate($object, new DateRange(
            'foo',
            'bar',
            '01010',
        ));
    }

    public function testSkipValidation(): void
    {
        $this->validator->validate(null, new DateRange(
            'foo',
            'bar',
            '01010',
        ));

        $this->assertNoViolation();
    }

    public function testWithInvalidDateRange(): void
    {
        $object = new \stdClass();
        $object->startDate = new \DateTime('2018-05-15 15:00:00+02:00');
        $object->endDate = new \DateTime('2018-05-20 15:00:00+02:00');

        $this->validator->validate($object, new DateRange(
            'startDate',
            'endDate',
            '3 days',
        ));

        $this
            ->buildViolation('common.date_range.invalid_date')
            ->atPath('property.path.endDate')
            ->setParameter('{{ limit }}', '18 mai 2018, 15:00')
            ->assertRaised()
        ;
    }

    public function testWithInvalidInterval(): void
    {
        $object = new \stdClass();
        $object->startDate = new \DateTime('2018-05-15 15:00:00+02:00');
        $object->endDate = new \DateTime('2018-05-16 15:00:00+02:00');

        $this->validator->validate($object, $constraint = new DateRange(
            'startDate',
            'endDate',
            '3 days|5 days',
        ));

        $violation = $this->buildViolation('common.date_range.invalid_interval');

        $violation
            ->atPath('property.path.endDate')
            ->setParameter('{{ dateMin }}', '18 mai 2018, 15:00')
            ->setParameter('{{ dateMax }}', '20 mai 2018, 15:00')
            ->assertRaised()
        ;

        $object->endDate = new \DateTime('2018-05-22 15:00:00+02:00');

        $this->validator->validate($object, $constraint);

        $violation->buildNextViolation('common.date_range.invalid_interval')
            ->atPath('property.path.endDate')
            ->setParameter('{{ dateMin }}', '18 mai 2018, 15:00')
            ->setParameter('{{ dateMax }}', '20 mai 2018, 15:00')
            ->assertRaised()
        ;
    }

    protected function createValidator(): DateRangeValidator
    {
        return new DateRangeValidator(new PropertyAccessor());
    }
}
