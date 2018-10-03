<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Utils\PhoneNumberFormatter;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class ProcurationProxyFoundMail extends TransactionalMail
{
    const SUBJECT = 'Votre procuration';

    public static function createRecipientFor(ProcurationRequest $request): RecipientInterface
    {
        return new Recipient($request->getEmailAddress());
    }

    public static function createTemplateVarsFrom(ProcurationRequest $request, string $infoUrl): array
    {
        $proxy = $request->getFoundProxy();

        return [
            'target_firstname' => StringCleaner::htmlspecialchars($request->getFirstNames()),
            'info_link' => $infoUrl,
            'elections' => implode(', ', $request->getElectionRoundLabels()),
            'voter_first_name' => StringCleaner::htmlspecialchars($proxy->getFirstNames()),
            'voter_last_name' => StringCleaner::htmlspecialchars($proxy->getLastName()),
            'voter_phone' => PhoneNumberFormatter::format($proxy->getPhone()),
            'mandant_first_name' => StringCleaner::htmlspecialchars($request->getFirstNames()),
            'mandant_last_name' => StringCleaner::htmlspecialchars($request->getLastName()),
            'mandant_phone' => PhoneNumberFormatter::format($request->getPhone()),
        ];
    }
}

