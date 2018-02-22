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
            static::getTemplateVars($host, $committee, $committeeUrl)
        );
    }

    private static function getTemplateVars(Adherent $host, Committee $committee, string $committeeUrl): array
    {
        return [
            'first_name' => self::escape($host->getFirstName()),
            'committee_city' => $committee->getCityName(),
            'committee_url' => $committeeUrl,
        ];
    }
}
