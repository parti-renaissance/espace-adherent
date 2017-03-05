<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Ramsey\Uuid\Uuid;

final class CommitteeNewFollowerMessage extends MailjetMessage
{
    public static function create(Committee $committee, array $hosts, Adherent $newFollower): self
    {
        if (!$hosts) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $host = array_shift($hosts);
        if (!$host instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $message = new static(
            Uuid::uuid4(),
            '54904',
            $host->getEmailAddress(),
            $host->getFullName(),
            'Un nouveau membre vient de suivre votre comité',
            self::getTemplateVars($committee, $newFollower),
            self::getRecipientVars($host)
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

    private static function getTemplateVars(Committee $committee, Adherent $newFollower): array
    {
        return [
            'committee_name' => self::escape($committee->getName()),
            'member_firstname' => self::escape($newFollower->getFirstName()),
            'member_lastname' => $newFollower->getLastNameInitial(),
            'member_age' => $newFollower->getAge(),
        ];
    }

    private static function getRecipientVars(Adherent $host): array
    {
        return [
            'animator_firstname' => self::escape($host->getFullName()),
        ];
    }
}
