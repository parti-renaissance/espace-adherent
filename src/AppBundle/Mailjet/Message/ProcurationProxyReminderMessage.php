<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\ProcurationRequest;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyReminderMessage extends MailjetMessage
{
    public static function create(ProcurationRequest $request, string $infosUrl): self
    {
        $proxy = $request->getFoundProxy();
        $message = new self(
            Uuid::uuid4(),
            '133881',
            $request->getEmailAddress(),
            null,
            'RAPPEL : votre procuration',
            [
                'target_firstname' => self::escape($request->getFirstNames()),
                'voter_first_name' => self::escape($proxy->getFirstNames()),
                'voter_last_name' => self::escape($proxy->getLastName()),
                'info_link' => $infosUrl,
                'elections' => implode(', ', $request->getElections()),
                'mandant_first_name' => self::escape($request->getFirstNames()),
                'mandant_last_name' => self::escape($request->getLastName()),
            ]
        );

        $message->setSenderName('Procuration Macron');
        if ($foundBy = $request->getFoundBy()) {
            $message->addCC($foundBy->getEmailAddress());
        }
        $message->addCC($proxy->getEmailAddress());

        return $message;
    }
}
