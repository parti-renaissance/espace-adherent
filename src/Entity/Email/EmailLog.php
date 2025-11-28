<?php

declare(strict_types=1);

namespace App\Entity\Email;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Mailer\Message\Message;
use App\Repository\Email\EmailLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: EmailLogRepository::class)]
#[ORM\Table(name: 'emails')]
class EmailLog
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * The message class namespace.
     */
    #[ORM\Column(length: 55, nullable: true)]
    private $messageClass;

    #[ORM\Column(length: 100)]
    private $sender;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $recipients;

    /**
     * The API request JSON payload.
     */
    #[ORM\Column(type: 'text')]
    private $requestPayload;

    /**
     * The successful API response JSON payload.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $responsePayload;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $deliveredAt;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $useTemplateEndpoint = true;

    public function __construct(
        UuidInterface $uuid,
        string $messageClass,
        string $sender,
        array $recipients,
        string $requestPayload,
        bool $useTemplateEndpoint = true,
    ) {
        $this->uuid = $uuid;
        $this->messageClass = $messageClass;
        $this->sender = $sender;
        $this->recipients = $recipients;
        $this->requestPayload = base64_encode($requestPayload);
        $this->useTemplateEndpoint = $useTemplateEndpoint;
    }

    public function __toString(): string
    {
        return $this->messageClass.' de '.$this->sender.' à '.\count($this->recipients).' destinataires';
    }

    public static function createFromMessage(Message $message, string $requestPayload, bool $fromTemplate = true): self
    {
        $recipients = [];

        foreach ($message->getRecipients() as $recipient) {
            $recipients[] = $recipient->getFullName().' <'.$recipient->getEmailAddress().'>';
        }

        $parts = explode('\\', $message::class);

        $senderName = $message->getSenderName() ?? 'Renaissance';

        return new self(
            $message->getUuid(),
            end($parts),
            $message->getReplyTo() ?? $senderName,
            $recipients,
            $requestPayload,
            $fromTemplate
        );
    }

    public function delivered(?string $responsePayload): void
    {
        $this->responsePayload = base64_encode($responsePayload);
        $this->deliveredAt = new \DateTime();
    }

    public function getMessageClass(): string
    {
        return $this->messageClass;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getRecipientsAsString(): string
    {
        return implode(",\n", $this->recipients);
    }

    public function getRequestPayload(): ?array
    {
        return $this->requestPayload ? json_decode(base64_decode($this->requestPayload), true) : null;
    }

    public function getRequestPayloadJson(): ?string
    {
        return $this->requestPayload ? base64_decode($this->requestPayload) : null;
    }

    public function getResponsePayload(): ?array
    {
        return $this->responsePayload ? json_decode(base64_decode($this->responsePayload), true) : null;
    }

    public function getResponsePayloadJson(): ?string
    {
        return $this->responsePayload ? base64_decode($this->responsePayload) : null;
    }

    public function isDelivered(): bool
    {
        return null !== $this->responsePayload;
    }

    public function getDeliveredAt(): ?\DateTime
    {
        return $this->deliveredAt;
    }
}
