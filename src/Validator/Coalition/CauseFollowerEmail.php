<?php

namespace App\Validator\Coalition;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class CauseFollowerEmail extends Constraint
{
    public $errorPath = 'email_address';
    public $messageAdherentExists = 'cause_follower.adherent.exists';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
