<?php

namespace App\Mailer\Message\Procuration;

use App\Entity\ProcurationRequest;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyCancelledMessage extends AbstractProcurationMessage
{
    public static function create(ProcurationRequest $request): Message
    {
        $proxy = $request->getFoundProxy();
        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            'Annulation de la mise en relation',
            [
                'target_firstname' => self::escape($request->getFirstNames()),
                'voter_first_name' => $proxy->getFirstNames(),
                'voter_last_name' => $proxy->getLastName(),
            ]
        );

        $message->addCC($proxy->getEmailAddress());

        return self::updateSenderInfo($message);
    }
}
