<?php

namespace App\Mailer\Message\ThematicCommunity;

use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

class ThematicCommunityJoinedPendingMessage extends Message
{
    public static function create(ThematicCommunityMembership $membership): self
    {
        return new self(
            Uuid::uuid4(),
            $membership->getEmail(),
            $membership->getFirstName().' '.$membership->getLastName(),
            'Bienvenue !',
            [
                'membership' => $membership,
            ]
        );
    }
}
