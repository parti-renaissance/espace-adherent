<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use Ramsey\Uuid\Uuid;

class CitizenActionCreatedMessage extends Message
{
    public function create(CitizenAction $citizenAction, array $adherents): self
    {
        $creator = $citizenAction->getOrganizer();
        /** @var Adherent $adherent */
        $adherent = array_shift($adherents);

        $message = new self(
            Uuid::uuid4(),
            '326404',
            $adherent->getEmailAddress(),
            $adherent->getFirstName().' '.$adherent->getLastName(),
            '[Projets citoyens] Une nouvelle action citoyenne au sein de votre projet citoyen !',
            static::getTemplateVars($citizenAction),
            static::getRecipientVars($adherent),
            $creator ? $creator->getEmailAddress() : ''
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient(
                $adherent->getEmailAddress(),
                $adherent->getFirstName().' '.$adherent->getLastName(),
                static::getRecipientVars($adherent)
            );
        }

        return $message;
    }

    private static function getTemplateVars(CitizenAction $citizenAction): array
    {
        return [
            'citizen_action_name' => self::escape($citizenAction->getName()),
            'citizen_project_name' => self::escape($citizenAction->getCitizenProject()->getName()),
            'citizen_action_date' => self::escape($citizenAction->getBeginAt()->format('Y/m/d')),
            'citizen_action_hour' => self::escape($citizenAction->getBeginAt()->format('h:i')),
            'citizen_action_address' => self::escape($citizenAction->getAddress()),
            'host_firstname' => self::escape($citizenAction->getOrganizerName()),
            'citizen_action_slug' => self::escape($citizenAction->getSlug()),
        ];
    }

    private static function getRecipientVars(Adherent $adherent): array
    {
        return [
            'target_firstname' => self::escape($adherent->getFirstName()),
        ];
    }
}
