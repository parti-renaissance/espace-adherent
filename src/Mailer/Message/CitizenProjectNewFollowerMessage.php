<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

final class CitizenProjectNewFollowerMessage extends Message
{
    public static function create(CitizenProject $citizenProject, array $hosts, Adherent $newFollower): self
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
            static::getTemplateVars($citizenProject, $newFollower),
            [],
            $newFollower->getEmailAddress()
        );

        foreach ($hosts as $host) {
            if (!$host instanceof Adherent) {
                throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', Adherent::class));
            }

            $message->addRecipient(
                $host->getEmailAddress(),
                $host->getFullName()
            );
        }

        return $message;
    }

    private static function getTemplateVars(CitizenProject $citizenProject, Adherent $newFollower): array
    {
        return [
            'citizen_project_name' => self::escape($citizenProject->getName()),
            'follower_first_name' => self::escape($newFollower->getFirstName()),
            'follower_last_name' => $newFollower->getLastNameInitial(),
            'follower_age' => $newFollower->getAge() ?? 'n/a',
            'follower_city' => $newFollower->getCityName() ?? 'n/a',
        ];
    }
}
