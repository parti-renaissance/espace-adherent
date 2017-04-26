<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyFoundMessage extends MailjetMessage
{
    public static function create(
        Adherent $procurationManager,
        ProcurationRequest $request,
        ProcurationProxy $proxy,
        string $infosUrl
    ): self {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $requestPhone = $request->getPhone();
        $proxyPhone = $proxy->getPhone();

        $message = new self(
            Uuid::uuid4(),
            '120187',
            $request->getEmailAddress(),
            null,
            'Votre procuration',
            [
                'target_firstname' => self::escape($request->getFirstNames()),
                'voter_first_name' => self::escape($proxy->getFirstNames()),
                'voter_last_name' => self::escape($proxy->getLastName()),
                'info_link' => $infosUrl,
                'elections' => implode(', ', $request->getElections()),
                'mandant_first_name' => self::escape($request->getFirstNames()),
                'mandant_last_name' => self::escape($request->getLastName()),
                'mandant_phone' => $requestPhone ? self::escape($phoneUtil->format($requestPhone, PhoneNumberFormat::INTERNATIONAL)) : '',
                'voter_phone' => $proxyPhone ? self::escape($phoneUtil->format($proxyPhone, PhoneNumberFormat::INTERNATIONAL)) : '',
            ]
        );

        $message->setSenderName('Procuration Macron');
        $message->addCC($procurationManager->getEmailAddress());
        $message->addCC($proxy->getEmailAddress());
        $message->setReplyTo($proxy->getEmailAddress());

        return $message;
    }
}
