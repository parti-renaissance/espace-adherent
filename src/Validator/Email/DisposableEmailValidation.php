<?php

declare(strict_types=1);

namespace App\Validator\Email;

use App\Validator\Email\Reason\DisposableEmail;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\Validation\EmailValidation;

class DisposableEmailValidation implements EmailValidation
{
    private const DOMAINS = [
        '10minutemail.com',
        '33mail.com',
        'anonbox.net',
        'byebyemail.com',
        'cock.li',
        'dispostable.com',
        'emailna.co',
        'fakeinbox.com',
        'getairmail.com',
        'getnada.com',
        'getonemail.com',
        'gishpuppy.com',
        'guerrillamail.com',
        'jetable.org',
        'l33r.eu',
        'mail-temporaire.fr',
        'mailcatch.com',
        'mailde.de',
        'maildrop.cc',
        'mailinator.com',
        'mailinator2.com',
        'mailme24.com',
        'mailmetrash.com',
        'mailnesia.com',
        'mailsac.com',
        'mailslurp.com',
        'mailtothis.com',
        'mailzilla.org',
        'mintemail.com',
        'moakt.com',
        'mytemp.email',
        'notsharingmy.info',
        'sharklasers.com',
        'spamavert.com',
        'spambox.info',
        'spamgourmet.com',
        'temp-mail.org',
        'throwaway.org',
        'throwawaymail.com',
        'trash-mail.com',
        'trashmail.com',
        'tuta.io',
        'u2club.com',
        'uemail99.com',
        'yopmail.com',
        'yopmail.net',
    ];

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
