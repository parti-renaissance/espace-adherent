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
            $request->getFirstNames().' '.$request->getLastName(),
            'RAPPEL : votre procuration',
            [
                'target_firstname' => self::escape($request->getFirstNames()),
                'voter_first_name' => $proxy->getFirstNames(),
                'voter_last_name' => $proxy->getLastName(),
                'info_link' => $infosUrl,
                'elections' => implode(', ', $request->getElections()),
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
