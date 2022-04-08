<?php

namespace App\Mailer\Message\Procuration;

use App\Entity\ProcurationRequest;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class ProcurationRequestReminderMessage extends AbstractProcurationMessage
{
    public static function create(ProcurationRequest $request): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            'Avez-vous validé votre demande de procuration ?',
            [],
            self::createRecipientVariables($request)
        );

        return self::updateSenderInfo($message);
    }

    public static function createRecipientVariables(ProcurationRequest $request): array
    {
        return [
            'first_name' => self::escape($request->getFirstNames()),
            'last_name' => self::escape($request->getLastName()),
        ];
    }
}
