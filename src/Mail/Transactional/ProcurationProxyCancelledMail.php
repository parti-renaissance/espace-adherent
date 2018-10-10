<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class ProcurationProxyCancelledMail extends TransactionalMail
{
    public const SUBJECT = 'Annulation de la mise en relation';

    public static function createRecipient(ProcurationRequest $request): RecipientInterface
    {
        return new Recipient($request->getEmailAddress());
    }

    public static function createTemplateVars(ProcurationRequest $request): array
    {
        $proxy = $request->getFoundProxy();

        return [
            'target_firstname' => StringCleaner::htmlspecialchars($request->getFirstNames()),
            'voter_first_name' => $proxy->getFirstNames(),
            'voter_last_name' => $proxy->getLastName(),
        ];
    }

    public static function createReplyToFromEmail(string $email): RecipientInterface
    {
        return new Recipient($email);
    }

    public static function createSender(): SenderInterface
    {
        return new Sender(null, 'Procuration En Marche !');
    }

    public static function createCcRecipients(ProcurationProxy $proxy): array
    {
        return [new Recipient($proxy->getEmailAddress())];
    }
}
