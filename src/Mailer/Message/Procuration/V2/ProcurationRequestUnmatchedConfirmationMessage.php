<?php

namespace App\Mailer\Message\Procuration\V2;

use App\Entity\Adherent;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\Round;
use App\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

final class ProcurationRequestUnmatchedConfirmationMessage extends AbstractProcurationMessage
{
    public static function create(Request $request, Proxy $proxy, Round $round, ?Adherent $matcher = null): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $request->email,
            null,
            'Mise en relation annulée',
            [
                'mandant_first_name' => self::escape($request->firstNames),
                'mandant_last_name' => self::escape($request->lastName),
                'voter_first_name' => self::escape($proxy->firstNames),
                'voter_last_name' => self::escape($proxy->lastName),
                'round_name' => self::escape($round->name),
                'round_date' => self::formatDate($round->date, 'd/m/Y'),
            ]
        );

        $message->addCC($proxy->email);

        if ($matcher) {
            $message->addCC($matcher->getEmailAddress());
        }

        $message->addBCC(self::SENDER_EMAIL);
        $message->setReplyTo($proxy->email);
        $message->setPreserveRecipients(true);

        return self::updateSenderInfo($message);
    }
}
