<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Invitation was already sent recently constraint.
 *
 * @Annotation
 */
class WasNotInvitedRecently extends Constraint
{
    public const WAS_INVITED_RECENTLY = 'was_invited_recently';

    public $message = 'Cette personne a déjà été invitée récemment, mais merci de votre proposition !';
    public $emailField = 'email';
    public $since = '24 hours';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
