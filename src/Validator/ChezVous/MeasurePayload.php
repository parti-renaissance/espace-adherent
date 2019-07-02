<?php

namespace AppBundle\Validator\ChezVous;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MeasurePayload extends Constraint
{
    public $unexpectedKeyForType = 'L\'information "{{ key }}" ne fait pas partie du type "{{ type }}".';
    public $missingKeyForType = 'L\'information "{{ key }}" est manquante pour le type "{{ type }}".';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
