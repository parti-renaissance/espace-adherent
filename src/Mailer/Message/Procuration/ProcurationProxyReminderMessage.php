<?php

namespace App\Mailer\Message\Procuration;

use App\Entity\ProcurationProxy;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyReminderMessage extends AbstractProcurationMessage
{
    public static function create(ProcurationProxy $proxy): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $proxy->getEmailAddress(),
            null,
            'Mandataire, le 10 avril, n\'oubliez pas de voter !',
            [],
            self::createRecipientVariables($proxy)
        );

        return self::updateSenderInfo($message);
    }

    public static function createRecipientVariables(ProcurationProxy $proxy): array
    {
        return [
            'first_name' => $proxy->getFirstNames(),
            'last_name' => $proxy->getLastName(),
        ];
    }
}
