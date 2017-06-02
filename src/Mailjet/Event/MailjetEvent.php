<?php

namespace AppBundle\Mailjet\Event;

use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Mailjet\MailjetTemplateEmail;
use AppBundle\Mailjet\Message\MailjetMessage;
use Symfony\Component\EventDispatcher\Event;

class MailjetEvent extends Event
{
    private $message;
    private $email;
    private $exception;

    public function __construct(
        MailjetMessage $message,
        MailjetTemplateEmail $email,
        MailjetException $exception = null
    ) {
        $this->message = $message;
        $this->email = $email;
        $this->exception = $exception;
    }

    public function getMessage(): MailjetMessage
    {
        return $this->message;
    }

    public function getEmail(): MailjetTemplateEmail
    {
        return $this->email;
    }

    public function getException()
    {
        return $this->exception;
    }
}
