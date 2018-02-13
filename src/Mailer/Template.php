<?php

namespace AppBundle\Mailer;

class Template
{
    private $name;
    private $senderName;
    private $senderEmail;
    private $subject;
    private $content;

    public function __construct(string $name, string $senderName, string $senderEmail, string $subject, string $content)
    {
        $this->name = $name;
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
        $this->subject = $subject;
        $this->content = $content;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSenderName(): string
    {
        return $this->senderName;
    }

    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    public function getFrom(): string
    {
        return sprintf('"%s" <%s>', $this->senderName, $this->senderEmail);
    }
}
