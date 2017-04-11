<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\ProcurationRequest;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyReminderMessage extends MailjetMessage
{
    public static function create(ProcurationRequest $request, string $infosUrl): self
    {
        $message = new self(
            Uuid::uuid4(),
            '133881',
            $request->getEmailAddress(),
            $request->getFirstNames().' '.$request->getLastName(),
            'RAPPEL : votre procuration',
            [
                'target_firstname' => self::escape($request->getFirstNames()),
                'voter_first_name' => $request->getFoundProxy()->getFirstNames(),
                'voter_last_name' => $request->getFoundProxy()->getLastName(),
                'info_link' => $infosUrl,
            ]
        );

        $message->setSenderName('Procuration Macron');
        if ($request->getFoundBy()) {
            $message->addCC($request->getFoundBy()->getEmailAddress());
        }
        $message->addCC($request->getFoundProxy()->getEmailAddress());

        return $message;
    }
}
