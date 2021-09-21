<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class RiposteOpenGraph extends Constraint
{
    public $message = 'riposte.open_graph.can_not_fetch';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
