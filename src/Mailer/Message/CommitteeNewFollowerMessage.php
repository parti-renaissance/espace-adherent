<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\Committee;
use Ramsey\Uuid\Uuid;

final class CommitteeNewFollowerMessage extends Message
{
    public static function create(Committee $committee, array $hosts, Adherent $newFollower, string $hostUrl): self
    {
        if (!$hosts) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $host = array_shift($hosts);
        if (!$host instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $message = new self(
            Uuid::uuid4(),
            $host->getEmailAddress(),
            $host->getFullName(),
            'Un nouveau membre vient de suivre votre comité',
            self::getTemplateVars($committee, $newFollower, $hostUrl),
            self::getRecipientVars($host),
            $newFollower->getEmailAddress()
        );

        foreach ($hosts as $host) {
            $message->addRecipient(
                $host->getEmailAddress(),
                $host->getFullName(),
                static::getRecipientVars($host)
            );
        }

        return $message;
    }

    private static function getTemplateVars(Committee $committee, Adherent $newFollower, string $hostUrl): array
    {
        return [
            'committee_name' => self::escape($committee->getName()),
            'committee_admin_url' => $hostUrl,
            'member_firstname' => self::escape($newFollower->getFirstName()),
            'member_lastname' => $newFollower->getLastNameInitial(),
            'member_age' => $newFollower->getAge() ?? 'n/a',
            'member_city' => $newFollower->getCityName() ?? 'n/a',
        ];
    }

    private static function getRecipientVars(Adherent $host): array
    {
        return [
            'animator_firstname' => self::escape($host->getFullName()),
        ];
    }
}
