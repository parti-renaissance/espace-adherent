<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\MyTeam\DelegatedAccess;
use Ramsey\Uuid\Uuid;

class DelegatedAccessCreatedMessage extends AbstractRenaissanceMessage
{
    public static function create(
        DelegatedAccess $delegatedAccess,
        string $zones,
        string $delegatorRole,
        string $primaryLink,
    ): self {
        $delegator = $delegatedAccess->getDelegator();
        $delegated = $delegatedAccess->getDelegated();

        return new self(
            Uuid::uuid4(),
            $delegated->getEmailAddress(),
            $delegated->getFullName(),
            'Nouvel accès délégué',
            [],
            [
                'first_name' => self::escape($delegated->getFirstName()),
                'delegator_first_name' => self::escape($delegator->getFirstName()),
                'delegator_last_name' => self::escape($delegator->getLastName()),
                'delegator_role_name' => self::escape($delegatorRole),
                'delegated_role_name' => self::escape($delegatedAccess->getRole()),
                'zone' => self::escape($zones),
                'primary_link' => $primaryLink,
            ]
        );
    }
}
