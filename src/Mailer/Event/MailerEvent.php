<?php

namespace AppBundle\Mailer\Event;

use AppBundle\Mailer\EmailTemplate;
use AppBundle\Mailer\Exception\MailerException;
use AppBundle\Mailer\Message\Message;
use Symfony\Component\EventDispatcher\Event;

class MailerEvent extends Event
{
    private $message;
    private $email;
    private $exception;

    public function __construct(Message $message, EmailTemplate $email, MailerException $exception = null)
    {
        $this->message = $message;
        $this->email = $email;
        $this->exception = $exception;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getEmail(): EmailTemplate
    {
        return $this->email;
    }

    public function getException()
    {
        return $this->exception;
    }
}
