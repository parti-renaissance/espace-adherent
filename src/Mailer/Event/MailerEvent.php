<?php

namespace App\Mailer\Event;

use App\Mailer\AbstractEmailTemplate;
use App\Mailer\Exception\MailerException;
use App\Mailer\Message\Message;
use Symfony\Contracts\EventDispatcher\Event;

class MailerEvent extends Event
{
    private $message;
    private $email;
    private $exception;

    public function __construct(
        Message $message,
        AbstractEmailTemplate $email = null,
        MailerException $exception = null
    ) {
        $this->message = $message;
        $this->email = $email;
        $this->exception = $exception;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getEmail(): ?AbstractEmailTemplate
    {
        return $this->email;
    }

    public function getException(): ?MailerException
    {
        return $this->exception;
    }
}
