<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Utils\PhoneNumberFormatter;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyReminderMessage extends Message
{
    public static function create(ProcurationRequest $request, string $infosUrl): self
    {
        $proxy = $request->getFoundProxy();

        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            static::getTemplateVars($request, $proxy, $infosUrl)
        );

        $message->setSenderName('Procuration En Marche !');
        $message->addRecipient($proxy->getEmailAddress());

        return $message;
    }

    private static function getTemplateVars(ProcurationRequest $request, ProcurationProxy $proxy, string $infosUrl): array
    {
        return [
            'info_url' => $infosUrl,
            'voter_first_name' => self::escape($proxy->getFirstNames()),
            'voter_phone' => PhoneNumberFormatter::format($proxy->getPhone()),
            'mandant_first_name' => self::escape($request->getFirstNames()),
            'mandant_phone' => PhoneNumberFormatter::format($request->getPhone()),
        ];
    }
}
