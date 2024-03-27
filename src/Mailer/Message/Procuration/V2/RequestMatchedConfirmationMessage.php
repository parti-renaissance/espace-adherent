<?php

namespace App\Mailer\Message\Procuration\V2;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Mailer\Message\Message;
use App\Utils\PhoneNumberUtils;
use Ramsey\Uuid\Uuid;

final class RequestMatchedConfirmationMessage extends AbstractProcurationMessage
{
    public static function create(Request $request, Proxy $proxy): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $request->email,
            null,
            sprintf(
                'Mise en relation : %s et %s',
                $request->firstNames,
                $proxy->firstNames
            ),
            [
                'mandant_first_name' => self::escape($request->firstNames),
                'mandant_last_name' => self::escape($request->lastName),
                'mandant_vote_place' => self::escape(
                    $request->customVotePlace ?? $request->votePlace
                ),
                'mandant_phone' => $request->phone ? PhoneNumberUtils::format($request->phone) : null,
                'mandant_email' => self::escape($request->email),
                'voter_first_name' => self::escape($proxy->firstNames),
                'voter_last_name' => self::escape($proxy->lastName),
                'voter_number' => self::escape($proxy->electorNumber),
                'voter_birthdate' => self::escape($proxy->birthdate->format('d/m/Y')),
                'voter_phone' => $proxy->phone ? PhoneNumberUtils::format($proxy->phone) : null,
                'voter_email' => self::escape($proxy->email),

            ]
        );

        $message->addCC($proxy->email);
        $message->setReplyTo($proxy->email);
        $message->setPreserveRecipients(true);

        return self::updateSenderInfo($message);
    }
}
