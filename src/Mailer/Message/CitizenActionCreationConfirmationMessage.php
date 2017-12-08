<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenAction;
use Ramsey\Uuid\Uuid;

final class CitizenActionCreationConfirmationMessage extends Message
{
    public static function create(CitizenAction $action): self
    {
        $author = $action->getOrganizer();

        return new self(
            Uuid::uuid4(),
            '196483',
            $author->getEmailAddress(),
            $author->getFirstName(),
            'Votre action citoyenne En Marche en attente de validation',
            static::getTemplateVars($action->getName()),
            static::getRecipientVars($author->getFirstName())
        );
    }

    private static function getTemplateVars(string $actionName): array
    {
        return [
            'IC_name' => self::escape($actionName),
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'prenom' => self::escape($firstName),
        ];
    }
}
