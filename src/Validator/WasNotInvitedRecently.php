<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Invitation was already sent recently constraint.
 */
#[\Attribute]
class WasNotInvitedRecently extends Constraint
{
    public $message = 'Cette personne a déjà été invitée récemment, mais merci de votre proposition !';
    public $emailField = 'email';
    public $since = '24 hours';

    public function __construct(
        ?string $emailField = null,
        ?string $since = null,
        ?string $message = null,
        $options = null,
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        $this->emailField = $emailField ?? $this->emailField;
        $this->since = $since ?? $this->since;
        $this->message = $message ?? $this->message;
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
