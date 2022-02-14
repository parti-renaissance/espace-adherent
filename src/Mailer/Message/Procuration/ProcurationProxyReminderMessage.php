<?php

namespace App\Mailer\Message\Procuration;

use App\Entity\ProcurationRequest;
use App\Mailer\Message\Message;
use App\Utils\PhoneNumberUtils;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyReminderMessage extends AbstractProcurationMessage
{
    public static function create(ProcurationRequest $request, string $infoUrl): Message
    {
        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            'RAPPEL : votre procuration',
            self::createRecipientVariables($request, $infoUrl)
        );

        $message->addRecipient($request->getFoundProxy()->getEmailAddress());

        return self::updateSenderInfo($message);
    }

    public static function createRecipientVariables(ProcurationRequest $request, string $infoUrl): array
    {
        $proxy = $request->getFoundProxy();

        return [
            'info_link' => $infoUrl,
            'elections' => implode(', ', $request->getElectionRoundLabels()),
            'voter_first_name' => self::escape($proxy->getFirstNames()),
            'voter_last_name' => self::escape($proxy->getLastName()),
            'voter_phone' => PhoneNumberUtils::format($proxy->getPhone()),
            'mandant_first_name' => self::escape($request->getFirstNames()),
            'mandant_last_name' => self::escape($request->getLastName()),
            'mandant_phone' => PhoneNumberUtils::format($request->getPhone()),
        ];
    }
}
