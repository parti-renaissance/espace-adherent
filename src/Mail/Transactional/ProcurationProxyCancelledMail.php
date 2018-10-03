<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class ProcurationProxyCancelledMail extends TransactionalMail
{
    const SUBJECT = 'Annulation de la mise en relation';

    public static function createRecipientFor(ProcurationRequest $request): RecipientInterface
    {
        return new Recipient($request->getEmailAddress());
    }

    public static function createTemplateVarsFrom(ProcurationRequest $request): array
    {
        $proxy = $request->getFoundProxy();

        return [
            'target_firstname' => StringCleaner::htmlspecialchars($request->getFirstNames()),
            'voter_first_name' => $proxy->getFirstNames(),
            'voter_last_name' => $proxy->getLastName(),
        ];
    }
}

