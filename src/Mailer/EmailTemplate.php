<?php

namespace AppBundle\Mailer;

use AppBundle\Mailer\Message\Message;
use Ramsey\Uuid\UuidInterface;

abstract class EmailTemplate implements \JsonSerializable
{
    protected $uuid;
    protected $senderEmail;
    protected $senderName;
    protected $replyTo;
    protected $cc;
    protected $recipients;
    protected $template;
    private $httpRequestPayload;
    private $httpResponsePayload;

    public function __construct(
        UuidInterface $uuid,
        string $template,
        string $senderEmail,
        string $senderName = null,
        string $replyTo = null,
        array $cc = []
    ) {
        $this->uuid = $uuid;
        $this->template = $template;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->replyTo = $replyTo;
        $this->cc = $cc;
        $this->recipients = [];
    }

    public static function createWithMessage(Message $message, string $template, string $defaultSenderEmail, string $defaultSenderName = null): self
    {
        $senderEmail = $message->getSenderEmail() ?: $defaultSenderEmail;
        $senderName = $message->getSenderName() ?: $defaultSenderName;

        $email = new static($message->getUuid(), $template, $senderEmail, $senderName, $message->getReplyTo(), $message->getCC());

        foreach ($message->getRecipients() as $recipient) {
            $email->addRecipient($recipient->getEmailAddress(), $recipient->getFullName(), $recipient->getVars());
        }

        return $email;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function delivered(string $httpResponsePayload, string $httpRequestPayload = null): void
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

    public function jsonSerialize(): array
    {
        return $this->getBody();
    }

    abstract public function addRecipient(string $email, string $name = null, array $vars = []);

    abstract public function getBody(): array;
}
