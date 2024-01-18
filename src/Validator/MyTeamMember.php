<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class MyTeamMember extends Constraint
{
    public $messageInvalidAdherentSource = "Ce militant ne peut pas être ajouter à l'équipe.";
    public $messageCurrentUser = 'Vous ne pouvez pas ajouter votre compte ou le compte qui vous a délégué l\'accès';
}
