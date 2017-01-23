<?php

namespace AppBundle\Mailjet\Message;

use Ramsey\Uuid\UuidInterface;

abstract class MailjetMessage
{
    private $uuid;
    private $vars;
    private $subject;
    private $template;
    private $recipientEmail;
    private $recipientName;

    final public function __construct(UuidInterface $uuid, $template, $recipientEmail, $recipientName, $subject, array $vars = [])
    {
        $this->uuid = $uuid;
        $this->recipientName = $recipientName;
        $this->recipientEmail = $recipientEmail;
        $this->template = $template;
        $this->subject = $subject;
        $this->vars = $vars;
    }

    protected function setVar(string $key, $value)
    {
        $this->vars[$key] = $value;
    }

    final public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    final public function getVars(): array
    {
        return $this->vars;
    }

    final public function getSubject(): string
    {
        return $this->subject;
    }

    final public function getRecipient(): array
    {
        return [$this->recipientEmail, $this->recipientName];
    }

    final public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }

    final public function getTemplate(): string
    {
        return $this->template;
    }
}
