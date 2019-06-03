<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Invitation was already sent recently constraint.
 *
 * @Annotation
 */
class WasNotInvitedRecently extends Constraint
{
    const WAS_INVITED_RECENTLY = 'was_invited_recently';

    protected static $errorNames = [
        self::WAS_INVITED_RECENTLY => 'WAS_INVITED_RECENTLY',
    ];

    public $message = 'Cette personne a déjà été invitée récemment, mais merci de votre proposition !';
    public $emailField = 'email';
    public $since = '24 hours';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
