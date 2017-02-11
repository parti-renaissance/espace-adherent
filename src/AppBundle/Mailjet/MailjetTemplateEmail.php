<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Mailjet\Message\MailjetMessage;

final class MailjetTemplateEmail implements \JsonSerializable
{
    private $senderEmail;
    private $senderName;
    private $subject;
    private $recipients;
    private $template;
    private $httpRequestPayload;
    private $httpResponsePayload;

    public function __construct(string $template, string $subject, string $senderEmail, string $sendName = null)
    {
        $this->senderName = $sendName;
        $this->senderEmail = $senderEmail;
        $this->subject = $subject;
        $this->template = $template;
        $this->recipients = [];
    }

    public static function createWithMailjetMessage(MailjetMessage $message, string $senderEmail, string $senderName = null): self
    {
        $email = new self($message->getTemplate(), $message->getSubject(), $senderEmail, $senderName);

        foreach ($message->getRecipients() as $recipient) {
            $email->addRecipient($recipient->getEmailAddress(), $recipient->getFullName(), $recipient->getVars());
        }

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
            throw new MailjetException('The Mailjet email requires at least one recipient.');
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

    public function delivered(string $httpResponsePayload, string $httpRequestPayload = null)
    {
        if ($httpRequestPayload) {
            $this->httpRequestPayload = $httpRequestPayload;
        }

        $this->httpResponsePayload = $httpResponsePayload;
    }

    public function getHttpRequestPayload(): string
    {
        if (!$this->httpRequestPayload) {
            $this->httpRequestPayload = json_encode($this->getBody());
        }

        return $this->httpRequestPayload;
    }

    public function getHttpResponsePayload(): ?string
    {
        return $this->httpResponsePayload;
    }

    public function jsonSerialize(): string
    {
        $body = $this->getBody();

        $this->httpRequestPayload = json_encode($body);

        return $body;
    }
}
