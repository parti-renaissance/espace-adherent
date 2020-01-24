<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Utils\PhoneNumberFormatter;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyFoundMessage extends Message
{
    public static function create(ProcurationRequest $request, string $infosUrl): self
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
                'voter_phone' => PhoneNumberFormatter::format($proxy->getPhone()),
                'mandant_first_name' => self::escape($request->getFirstNames()),
                'mandant_last_name' => self::escape($request->getLastName()),
                'mandant_phone' => PhoneNumberFormatter::format($request->getPhone()),
            ]
        );

        $message->addCC($request->getFoundBy()->getEmailAddress());
        $message->addCC($proxy->getEmailAddress());
        $message->setReplyTo($proxy->getEmailAddress());

        return $message;
    }
}
