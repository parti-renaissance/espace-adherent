<?php

namespace App\Validator\ChezVous;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MeasurePayload extends Constraint
{
    public $unexpectedKeyForType = 'L\'information "{{ key }}" ne fait pas partie du type "{{ type }}".';
    public $missingKeyForType = 'L\'information "{{ key }}" est manquante pour le type "{{ type }}".';
    public $defineAtLeastOneKeyForType = 'Veuillez renseigner au moins une information parmis ({{ keys }}) pour la mesure "{{ type }}".';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
