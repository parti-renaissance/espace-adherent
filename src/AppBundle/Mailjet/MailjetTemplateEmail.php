<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailjet\Message\MailjetMessage;

final class MailjetTemplateEmail
{
    private $senderEmail;
    private $senderName;
    private $subject;
    private $recipients;
    private $template;

    public function __construct(string $template, string $subject, string $senderEmail, string $sendName = null)
    {
        $this->senderName = $sendName;
        $this->senderEmail = $senderEmail;
        $this->subject = $subject;
        $this->template = $template;
        $this->recipients = [];
    }

    public static function createWithMailjetMessage(MailjetMessage $message, string $senderEmail, string $sendName = null): self
    {
        $recipient = $message->getRecipient();

        $email = new self($message->getTemplate(), $message->getSubject(), $senderEmail, $sendName);
        $email->addRecipient($recipient[0], $recipient[1], $message->getVars());

        return $email;
    }

    public function addRecipient(string $email, string $name = null, array $vars = [])
    {
        $recipient['Email'] = $email;

        if ($name) {
            $recipient['Name'] = $name;
        }

        if (count($vars)) {
            $recipient['Vars'] = $vars;
        }

        $this->recipients[] = $recipient;
    }

    public function getBody(): array
    {
        if (!count($this->recipients)) {
            throw new \LogicException('Recipient is missing!');
        }

        $body['FromEmail'] = $this->senderEmail;
        if ($this->senderName) {
            $body['FromName'] = $this->senderName;
        }

        $body['Subject'] = $this->subject;
        $body['MJ-TemplateID'] = $this->template;
        $body['MJ-TemplateLanguage'] = true;
        $body['Recipients'] = $this->recipients;

        return $body;
    }
}
