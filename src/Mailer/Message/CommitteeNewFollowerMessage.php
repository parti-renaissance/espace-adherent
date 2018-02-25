<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Ramsey\Uuid\Uuid;

final class CommitteeNewFollowerMessage extends Message
{
    public static function create(Committee $committee, array $hosts, Adherent $newFollower, string $hostUrl): self
    {
        if (!$hosts) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $host = array_shift($hosts);
        if (!$host instanceof Adherent) {
            throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
        }

        $message = new self(
            Uuid::uuid4(),
            $host->getEmailAddress(),
            $host->getFullName(),
            static::getTemplateVars($committee, $newFollower, $hostUrl),
            static::getRecipientVars($host),
            $newFollower->getEmailAddress()
        );

        foreach ($hosts as $host) {
            if (!$host instanceof Adherent) {
                throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
            }

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
            'member_first_name' => self::escape($newFollower->getFirstName()),
            'member_last_name' => $newFollower->getLastNameInitial(),
            'member_age' => $newFollower->getAge() ?? 'n/a',
        ];
    }

    private static function getRecipientVars(Adherent $host): array
    {
        return [
            'first_name' => self::escape($host->getFirstName()),
        ];
    }
}
