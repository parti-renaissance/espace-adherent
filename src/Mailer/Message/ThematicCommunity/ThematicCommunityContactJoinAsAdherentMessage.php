<?php

namespace App\Mailer\Message\ThematicCommunity;

use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

class ThematicCommunityContactJoinAsAdherentMessage extends Message
{
    public static function create(ThematicCommunityMembership $membership): self
    {
        return new self(
            Uuid::uuid4(),
            $membership->getEmail(),
            $membership->getFirstName().' '.$membership->getLastName(),
            'Connectez-vous pour rejoindre cette communauté',
            [
                'membership' => $membership,
            ],
        );
    }
}
