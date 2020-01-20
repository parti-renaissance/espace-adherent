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
            'Un nouveau membre a rejoint votre projet citoyen !',
            self::getTemplateVars($citizenProject, $newFollower),
            self::getRecipientVars($host),
            $newFollower->getEmailAddress()
        );

        foreach ($hosts as $host) {
            $message->addRecipient(
                $host->getEmailAddress(),
                $host->getFullName(),
                self::getRecipientVars($host)
            );
        }

        return $message;
    }

    private static function getTemplateVars(CitizenProject $citizenProject, Adherent $newFollower): array
    {
        return [
            'citizen_project_name' => self::escape($citizenProject->getName()),
            'follower_firstname' => self::escape($newFollower->getFirstName()),
            'follower_lastname' => $newFollower->getLastNameInitial(),
            'follower_age' => $newFollower->getAge() ?? 'n/a',
            'follower_city' => $newFollower->getCityName() ?? 'n/a',
        ];
    }

    private static function getRecipientVars(Adherent $host): array
    {
        return ['animator_firstname' => self::escape($host->getFirstName())];
    }
}
