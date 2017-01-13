<?php

namespace AppBundle\Mailjet\Message;

abstract class MailjetMessage
{
    private $vars;
    private $subject;
    private $template;
    private $recipientEmail;
    private $recipientName;

    final public function __construct($template, $recipientEmail, $recipientName, $subject, array $vars = [])
    {
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

    final public function getTemplate(): string
    {
        return $this->template;
    }
}
