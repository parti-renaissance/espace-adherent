<?php

declare(strict_types=1);

namespace App\Validator\Email;

use App\Repository\BannedAdherentRepository;
use App\Validator\Email\Reason\DisabledEmail;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\Validation\EmailValidation;

class DisabledEmailValidator implements EmailValidation
{
    private ?InvalidEmail $error = null;

    public function __construct(private readonly BannedAdherentRepository $bannedAdherentRepository)
    {
    }

    public function isValid(string $email, EmailLexer $emailLexer): bool
    {
        if ($this->bannedAdherentRepository->countForEmail($email)) {
            $this->error = new InvalidEmail(new DisabledEmail(), '');
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
