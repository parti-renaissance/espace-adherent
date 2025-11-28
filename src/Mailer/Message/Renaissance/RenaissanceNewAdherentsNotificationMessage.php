<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

class RenaissanceNewAdherentsNotificationMessage extends AbstractRenaissanceMessage
{
    public static function create(Adherent $manager, int $newSympathizersCount, int $newAdherentsCount, string $buttonUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $manager->getEmailAddress(),
            $manager->getFullName(),
            'Nouvelles inscriptions dans votre zone',
            [
                'new_sympathizers_count' => $newSympathizersCount,
                'new_adherents_count' => $newAdherentsCount,
                'button_url' => $buttonUrl,
            ]
        );
    }
}
