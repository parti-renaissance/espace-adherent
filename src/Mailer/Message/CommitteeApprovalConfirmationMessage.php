<?php

namespace App\Mailer\Message;

use App\Entity\ProvisionalSupervisor;
use Ramsey\Uuid\Uuid;

final class CommitteeApprovalConfirmationMessage extends Message
{
    public static function create(array $provisionalSupervisors, string $committeeCityName, string $committeeUrl): self
    {
        /** @var ProvisionalSupervisor[] $provisionalSupervisors */
        $provisionalSupervisor = array_shift($provisionalSupervisors);
        $adherent = $provisionalSupervisor->getAdherent();

        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre comité est validé, à vous de jouer',
            static::getTemplateVars($committeeCityName, $committeeUrl),
            static::getRecipientVars($adherent->getFirstName())
        );

        foreach ($provisionalSupervisors as $provisionalSupervisor) {
            $adherent = $provisionalSupervisor->getAdherent();
            $message->addRecipient(
                $adherent->getEmailAddress(),
                $adherent->getFullName(),
                static::getRecipientVars($adherent->getFirstName()),
            );
        }

        return $message;
    }

    private static function getTemplateVars(string $committeeCityName, string $committeeUrl): array
    {
        return [
            'committee_city' => $committeeCityName,
            'committee_url' => $committeeUrl,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'animator_firstname' => self::escape($firstName),
        ];
    }
}
