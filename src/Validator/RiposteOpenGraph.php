<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class RiposteOpenGraph extends Constraint
{
    public $message = 'riposte.open_graph.can_not_fetch';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
