<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Utils\PhoneNumberFormatter;
use Ramsey\Uuid\Uuid;

final class ProcurationProxyFoundMessage extends Message
{
    public static function create(
        ProcurationRequest $request,
        string $infosUrl
    ): self {
        $proxy = $request->getFoundProxy();
        $message = new self(
            Uuid::uuid4(),
            $request->getEmailAddress(),
            null,
            self::getTemplateVars(
                $request->getFirstNames(),
                $infosUrl,
                implode(', ', $request->getElectionRoundLabels()),
                $proxy->getFirstNames(),
                $proxy->getLastName(),
                PhoneNumberFormatter::format($proxy->getPhone()),
                $request->getFirstNames(),
                $request->getLastName(),
                PhoneNumberFormatter::format($request->getPhone())
            ),
            [],
            $proxy->getEmailAddress()
        );

        $message->setSenderName('Procuration En Marche !');
        $message->addCC($request->getFoundBy()->getEmailAddress());
        $message->addCC($proxy->getEmailAddress());

        return $message;
    }

    private static function getTemplateVars(
        string $targetFirstName,
        string $infoLink,
        string $elections,
        string $voterFirstName,
        string $voterLastName,
        string $voterPhone,
        string $mandantFirstName,
        string $mandantLastName,
        string $mandantPhone
    ): array {
        return [
            'target_firstname' => self::escape($targetFirstName),
            'info_link' => $infoLink,
            'elections' => $elections,
            'voter_first_name' => self::escape($voterFirstName),
            'voter_last_name' => self::escape($voterLastName),
            'voter_phone' => $voterPhone,
            'mandant_first_name' => self::escape($mandantFirstName),
            'mandant_last_name' => self::escape($mandantLastName),
            'mandant_phone' => $mandantPhone,
        ];
    }
}
