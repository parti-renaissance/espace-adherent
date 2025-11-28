<?php

declare(strict_types=1);

namespace App\Mailer;

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
    protected $messageHtmlContent;
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

    public function setPreserveRecipients(?bool $preserveRecipients): void
    {
        $this->preserveRecipients = $preserveRecipients;
    }

    public function setMessageHtmlContent(string $html): void
    {
        $this->messageHtmlContent = $html;
    }

    public function fromTemplate(): bool
    {
        return (bool) $this->template;
    }

    abstract public function addRecipient(string $email, ?string $name = null, array $vars = []);

    abstract public function getBody(): array;
}
