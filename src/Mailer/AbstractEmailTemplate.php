<?php

namespace App\Mailer;

use App\Mailer\Message\Message;
use Ramsey\Uuid\UuidInterface;

abstract class AbstractEmailTemplate implements \JsonSerializable, EmailTemplateInterface
{
    protected $uuid;
    protected $senderEmail;
    protected $senderName;
    protected $replyTo;
    protected $subject;
    protected $cc;
    protected $bcc;
    protected $recipients = [];
    protected $template;
    protected $vars;
    protected $templateContent;
    /** @var bool|null */
    protected $preserveRecipients;

    private $httpRequestPayload;
    private $httpResponsePayload;

    final public function __construct(
        UuidInterface $uuid,
        string $template,
        string $subject,
        string $senderEmail,
        ?string $senderName = null,
        ?string $replyTo = null,
        array $cc = [],
        array $bcc = [],
        array $vars = [],
        array $templateContent = [],
    ) {
        $this->uuid = $uuid;
        $this->template = $template;
        $this->subject = $subject;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->replyTo = $replyTo;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->vars = $vars;
        $this->templateContent = $templateContent;
    }

    public static function createWithMessage(
        Message $message,
        string $defaultSenderEmail,
        ?string $defaultSenderName = null,
    ): self {
        $senderEmail = $message->getSenderEmail() ?: $defaultSenderEmail;
        $senderName = $message->getSenderName() ?: $defaultSenderName;

        $email = new static(
            $message->getUuid(),
            $message->getTemplate() ?? $message->generateTemplateName(),
            $message->getSubject(),
            $senderEmail,
            $senderName,
            $message->getReplyTo(),
            $message->getCC(),
            $message->getBCC(),
            $message->getVars(),
            $message->getTemplateContent()
        );

        foreach ($message->getRecipients() as $recipient) {
            $email->addRecipient($recipient->getEmailAddress(), $recipient->getFullName(), $recipient->getVars());
        }

        $email->setPreserveRecipients($message->getPreserveRecipients());

        return $email;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function delivered(string $httpResponsePayload, ?string $httpRequestPayload = null): void
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

    abstract public function addRecipient(string $email, ?string $name = null, array $vars = []);

    abstract public function getBody(): array;

    private function setPreserveRecipients(?bool $preserveRecipients): void
    {
        $this->preserveRecipients = $preserveRecipients;
    }
}
