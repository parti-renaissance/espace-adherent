<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class DateRange extends Constraint
{
    public $startDateField;
    public $endDateField;
    public $interval;
    public $messageDate = 'common.date_range.invalid_date';
    public $messageInterval = 'common.date_range.invalid_interval';

    public function getRequiredOptions()
    {
        return ['startDateField', 'endDateField', 'interval'];
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
