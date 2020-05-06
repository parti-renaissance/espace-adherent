<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class CommitteeMember extends Constraint
{
    public $message = 'Vous ne pouvez sélectionner de comité dont vous n\'êtes pas membre.';
}
