<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Utils\PhoneNumberFormatter;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class ProcurationProxyReminderMail extends TransactionalMail
{
    public const SUBJECT = 'RAPPEL : votre procuration';

    public static function createRecipientsFor(ProcurationRequest $request): array
    {
        return [
            new Recipient($request->getEmailAddress()),
            new Recipient($request->getFoundProxy()->getEmailAddress()),
        ];
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

