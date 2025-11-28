<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class DateRange extends Constraint
{
    public $messageDate = 'common.date_range.invalid_date';
    public $messageInterval = 'common.date_range.invalid_interval';

    public function __construct(
        public readonly string $startDateField,
        public readonly string $endDateField,
        public readonly string $interval,
        ?string $messageInterval = null,
        ?string $messageDate = null,
        $options = null,
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        $this->messageInterval = $messageInterval ?? $this->messageInterval;
        $this->messageDate = $messageDate ?? $this->messageDate;
    }

    public function getTargets(): array|string
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
