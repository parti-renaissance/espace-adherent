<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AdherentForCommitteeMandateReplacement extends Constraint
{
    public function __construct(public readonly string $errorPath, $options = null, $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
