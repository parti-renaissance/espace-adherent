<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Ramsey\Uuid\Uuid;

final class CommitteeApprovalConfirmationMessage extends Message
{
    public static function create(Adherent $host, Committee $committee, string $committeeUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $host->getEmailAddress(),
            $host->getFullName(),
            static::getTemplateVars($committee, $committeeUrl),
            ['recipient_first_name' => self::escape($host->getFirstName())]
        );
    }

    private static function getTemplateVars(Committee $committee, string $committeeUrl): array
    {
        return [
            'committee_city' => $committee->getCityName(),
            'committee_url' => $committeeUrl,
        ];
    }
}
