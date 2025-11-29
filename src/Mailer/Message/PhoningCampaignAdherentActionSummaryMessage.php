<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Phoning\CampaignHistory;
use Ramsey\Uuid\Uuid;

class PhoningCampaignAdherentActionSummaryMessage extends Message
{
    public static function create(
        CampaignHistory $campaignHistory,
        ?string $emailSubscribeUrl,
        ?string $smsPreferenceUrl,
        ?string $editProfilUrl,
    ): self {
        return new self(
            Uuid::uuid4(),
            $campaignHistory->getAdherent()->getEmailAddress(),
            $campaignHistory->getAdherent()->getFullName(),
            'Suite à notre appel',
            [],
            [
                'first_name' => self::escape($campaignHistory->getAdherent()->getFirstName()),
                'email_subscribe_url' => $emailSubscribeUrl,
                'sms_preference_url' => $smsPreferenceUrl,
                'edit_profil_url' => $editProfilUrl,
            ]
        );
    }
}
