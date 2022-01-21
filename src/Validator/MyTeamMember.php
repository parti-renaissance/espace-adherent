<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class MyTeamMember extends Constraint
{
    public $messageInvalidAdherentSource = "Ce militant n'est pas disponible pour l'ajout dans l'équipe.";
    public $messageInvalidAdherent = 'Le militant choisi ne fait pas partie de la zone géographique que vous gérez.';
}
