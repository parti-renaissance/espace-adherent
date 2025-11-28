<?php

declare(strict_types=1);

namespace App\Mailer\Message\Procuration\V2;

use App\Entity\Adherent;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\Round;
use App\Mailer\Message\Message;
use App\Utils\PhoneNumberUtils;
use Ramsey\Uuid\Uuid;

final class ProcurationMatchReminderMessage extends AbstractProcurationMessage
{
    public static function create(Request $request, Proxy $proxy, Round $round, ?Adherent $matcher = null): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $request->email,
            null,
            '[PROCURATIONS] Avez-vous fait les démarches ?',
            [
                'mandant_first_name' => self::escape($request->firstNames),
                'mandant_last_name' => self::escape($request->lastName),
                'mandant_vote_place' => self::escape($request->getVotePlaceName() ?? ''),
                'mandant_birthdate' => self::escape($request->birthdate->format('d/m/Y')),
                'mandant_phone' => PhoneNumberUtils::format($request->phone),
                'mandant_email' => self::escape($request->email),
                'voter_first_name' => self::escape($proxy->firstNames),
                'voter_last_name' => self::escape($proxy->lastName),
                'voter_number' => self::escape($proxy->electorNumber ?? ''),
                'voter_birthdate' => self::escape($proxy->birthdate->format('d/m/Y')),
                'voter_phone' => PhoneNumberUtils::format($proxy->phone),
                'voter_email' => self::escape($proxy->email),
                'election_name' => self::escape($round->election->name),
                'round_name' => self::escape($round->name),
                'round_date' => self::formatDate($round->date, 'd MMMM y'),
            ]
        );

        $message->addCC($proxy->email);

        if ($matcher) {
            $message->addCC($matcher->getEmailAddress());
        }

        $message->setReplyTo($proxy->email);
        $message->setPreserveRecipients(true);

        return self::updateSenderInfo($message);
    }
}
