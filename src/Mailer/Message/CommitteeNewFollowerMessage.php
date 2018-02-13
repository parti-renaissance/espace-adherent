<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Ramsey\Uuid\Uuid;

final class CommitteeNewFollowerMessage extends Message
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

        $message = new self(
            Uuid::uuid4(),
            $host->getEmailAddress(),
            $host->getFullName(),
            self::getTemplateVars(
                $committee->getName(),
                $newFollower->getFirstName(),
                $newFollower->getLastNameInitial(),
                $newFollower->getCityName(),
                $newFollower->getAge()
            ),
            self::getRecipientVars($host->getFirstName()),
            $newFollower->getEmailAddress()
        );

        foreach ($hosts as $host) {
            if (!$host instanceof Adherent) {
                throw new \InvalidArgumentException('This message builder requires a collection of Adherent instances');
            }

            $message->addRecipient(
                $host->getEmailAddress(),
                $host->getFullName(),
                static::getRecipientVars($host->getFirstName())
            );
        }

        return $message;
    }

    private static function getTemplateVars(
        string $committeeName,
        string $newFollowerFirstName,
        string $newFollowerLastNameInitial,
        string $newFollowerCity,
        ?int $newFollowerAge
    ): array {
        return [
            'committee_name' => self::escape($committeeName),
            'member_firstname' => self::escape($newFollowerFirstName),
            'member_lastname' => $newFollowerLastNameInitial,
            'member_city' => $newFollowerCity,
            'member_age' => $newFollowerAge ?? 'n/a',
        ];
    }

    private static function getRecipientVars(string $animatorFirstName): array
    {
        return [
            'animator_firstname' => self::escape($animatorFirstName),
        ];
    }
}
