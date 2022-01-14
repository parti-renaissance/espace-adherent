<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class RiposteOpenGraph extends Constraint
{
    public $noOpenGraphMessage = 'riposte.open_graph.can_not_fetch';
    public $emptyOpenGraphTitleMessage = 'riposte.open_graph.empty_title';
    public $emptyOpenGraphDescriptionMessage = 'riposte.open_graph.empty_description';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
