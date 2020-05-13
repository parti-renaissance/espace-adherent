<?php

namespace Tests\App\Validator;

use App\Validator\DateRange;
use App\Validator\DateRangeValidator;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class DateRangeValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testStartDateFieldIsNotReadableThrowsException(): void
    {
        $this->validator->validate(new \stdClass(), new DateRange([
            'startDateField' => 'foo',
            'endDateField' => 'bar',
            'interval' => '01010',
        ]));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testInvalidStartDateThrowsException(): void
    {
        $object = new \stdClass();

        $object->foo = true;
        $object->bar = 'hello world';

        $this->validator->validate($object, new DateRange([
            'startDateField' => 'foo',
            'endDateField' => 'bar',
            'interval' => '01010',
        ]));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testEndDateFieldIsNotReadableThrowsException(): void
    {
        $object = new \stdClass();
        $object->foo = true;

        $this->validator->validate($object, new DateRange([
            'startDateField' => 'foo',
            'endDateField' => 'bar',
            'interval' => '01010',
        ]));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testInvalidEndDateThrowsException(): void
    {
        $object = new \stdClass();

        $object->foo = new \DateTime();
        $object->bar = 'hello world';

        $this->validator->validate($object, new DateRange([
            'startDateField' => 'foo',
            'endDateField' => 'bar',
            'interval' => '01010',
        ]));
    }

    public function testSkipValidation(): void
    {
        $this->validator->validate(null, new DateRange([
            'startDateField' => 'foo',
            'endDateField' => 'bar',
            'interval' => '01010',
        ]));

        $this->assertNoViolation();
    }

    public function testWithInvalidDateRange(): void
    {
        \Locale::setDefault('fr');

        $object = new \stdClass();
        $object->startDate = new \DateTime('2018-05-15 15:00:00+02:00');
        $object->endDate = new \DateTime('2018-05-20 15:00:00+02:00');

        $this->validator->validate($object, new DateRange([
            'startDateField' => 'startDate',
            'endDateField' => 'endDate',
            'interval' => '3 days',
        ]));

        $this
            ->buildViolation('common.date_range.invalid_interval')
            ->atPath('property.path.endDate')
            ->setParameter('{{ limit }}', '18 mai 2018 à 13:00') // in UTC
            ->assertRaised()
        ;
    }

    protected function createValidator()
    {
        return new DateRangeValidator(new PropertyAccessor());
    }
}
