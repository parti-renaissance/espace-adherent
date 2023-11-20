<?php

namespace App\Validator\Email;

use App\Validator\Email\Reason\DisposableEmail;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\Validation\EmailValidation;

class DisposableEmailValidation implements EmailValidation
{
    private const DOMAINS = ['yopmail.com', '10minutemail.com', 'guerrillamail.com', 'temp-mail.org', 'mailinator.com', 'dispostable.com', 'throwawaymail.com', 'sharklasers.com', 'getnada.com', 'maildrop.cc', 'mytemp.email', 'trash-mail.com', 'jetable.org', 'anonbox.net', 'mailnesia.com', 'mintemail.com', 'mailcatch.com', '33mail.com', 'getairmail.com', 'fakeinbox.com', 'trashmail.com', 'gishpuppy.com', 'mailmetrash.com', 'mailinator2.com', 'spamgourmet.com', 'notsharingmy.info', 'mailzilla.org', 'byebyemail.com', 'cock.li', 'mailtothis.com', 'mailslurp.com', 'mailsac.com', 'mailde.de', 'getonemail.com', 'spamavert.com', 'throwaway.org', 'yopmail.net', 'tuta.io', 'mail-temporaire.fr', 'dispostable.com', 'uemail99.com', 'u2club.com', 'mailinator2.com', 'emailna.co', 'l33r.eu', 'mailslurp.com', 'mailsac.com', 'mailde.de', 'getonemail.com', 'spamavert.com', 'throwaway.org', 'yopmail.net', 'tuta.io', 'mail-temporaire.fr', 'dispostable.com', 'uemail99.com', 'u2club.com', 'mailinator2.com', 'emailna.co', 'l33r.eu', 'moakt.com', 'spambox.info', 'mailme24.com'];

    private ?InvalidEmail $error = null;

    public function isValid(string $email, EmailLexer $emailLexer): bool
    {
        if (preg_match('/('.implode('|', self::DOMAINS).')/', $email)) {
            $this->error = new InvalidEmail(new DisposableEmail(), '');
        }

        return null === $this->error;
    }

    public function getError(): ?InvalidEmail
    {
        return $this->error;
    }

    public function getWarnings(): array
    {
        return [];
    }
}
