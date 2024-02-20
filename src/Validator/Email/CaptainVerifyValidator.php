<?php

namespace App\Validator\Email;

use App\CaptainVerify\CaptainVerifyDriver;
use App\Validator\Email\Reason\InvalidEmailByCaptainVerify;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\Validation\EmailValidation;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class CaptainVerifyValidator implements EmailValidation
{
    private ?InvalidEmail $error = null;

    public function __construct(private readonly CaptainVerifyDriver $captainVerifyDriver)
    {
    }

    public function isValid(string $email, EmailLexer $emailLexer): bool
    {
        try {
            $response = $this->captainVerifyDriver->verify($email);

            if (false === $response->isValid()) {
                $this->error = new InvalidEmail(new InvalidEmailByCaptainVerify(), '');
            }
        } catch (ExceptionInterface $e) {
            $this->error = new InvalidEmail(new InvalidEmailByCaptainVerify(), '');
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
