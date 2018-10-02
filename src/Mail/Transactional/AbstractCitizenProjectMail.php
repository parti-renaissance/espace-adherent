<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Mail\AdherentMailTrait;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

abstract class AbstractCitizenProjectMail extends TransactionalMail
{
    private const SENDER_EMAIL = 'projetscitoyens@en-marche.fr';

    use AdherentMailTrait;

    public static function createSender(): SenderInterface
    {
        return new Sender(self::SENDER_EMAIL, null);
    }
}
