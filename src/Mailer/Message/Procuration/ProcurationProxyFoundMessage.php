<?php

namespace App\Mailer\Message\Procuration;

use App\Entity\ProcurationRequest;
use App\Mailer\Message\Message;
use App\Utils\PhoneNumberUtils;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyFoundMessage extends AbstractProcurationMessage
{
    public static function create(ProcurationRequest $request, string $infosUrl): Message
    {
        $proxy = $request->getFoundProxy();
        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            'Votre procuration',
            [
                'info_link' => $infosUrl,
                'elections' => implode(', ', $request->getElectionRoundLabels()),
                'voter_first_name' => self::escape($proxy->getFirstNames()),
                'voter_last_name' => self::escape($proxy->getLastName()),
                'voter_phone' => PhoneNumberUtils::format($proxy->getPhone()),
                'mandant_first_name' => self::escape($request->getFirstNames()),
                'mandant_last_name' => self::escape($request->getLastName()),
                'mandant_phone' => PhoneNumberUtils::format($request->getPhone()),
            ]
        );

        $message->addCC($request->getFoundBy()->getEmailAddress());
        $message->addCC($proxy->getEmailAddress());
        $message->setReplyTo($proxy->getEmailAddress());
        $message->setPreserveRecipients(true);

        return self::updateSenderInfo($message);
    }
}
