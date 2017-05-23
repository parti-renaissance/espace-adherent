<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Mailjet\Message\MailjetMessage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class MailjetTemplateEmail implements \JsonSerializable
{
    private $uuid;
    private $senderEmail;
    private $senderName;
    private $replyTo;
    private $subject;
    private $cc;
    private $recipients;
    private $template;
    private $httpRequestPayload;
    private $httpResponsePayload;

    public function __construct(
        UuidInterface $uuid,
        string $template,
        string $subject,
        string $senderEmail,
        string $senderName = null,
        string $replyTo = null,
        array $cc = []
    ) {
        $this->uuid = $uuid;
        $this->template = $template;
        $this->subject = $subject;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->replyTo = $replyTo;
        $this->cc = $cc;
        $this->recipients = [];
    }

    public static function createWithMailjetMessage(MailjetMessage $message, string $defaultSenderEmail, string $defaultSenderName = null): self
    {
        $senderEmail = $message->getSenderEmail() ?: $defaultSenderEmail;
        $senderName = $message->getSenderName() ?: $defaultSenderName;

        $email = new self($message->getUuid(), $message->getTemplate(), $message->getSubject(), $senderEmail, $senderName, $message->getReplyTo(), $message->getCC());

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

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
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

        if ($this->cc) {
            $to = [];

            if ($this->recipients) {
                $body['Vars'] = $this->recipients[0]['Vars'];

                foreach ($this->recipients as $recipient) {
                    if (isset($recipient['Name'])) {
                        $to[] = sprintf('"%s" <%s>', $recipient['Name'], $recipient['Email']);
                    } else {
                        $to[] = $recipient['Email'];
                    }
                }
            }

            foreach ($this->cc as $cc) {
                $to[] = $cc;
            }

            $body['To'] = implode(', ', $to);
        } else {
            $body['Recipients'] = $this->recipients;
        }

        if ($this->replyTo) {
            $body['Headers'] = [
                'Reply-To' => $this->replyTo,
            ];
        }

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
