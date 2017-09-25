<?php

namespace AppBundle\Mailer\Model;

use AppBundle\Mailer\Message\Message;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class Email
{
    /**
     * The message class namespace.
     *
     * @ORM\Column(length=55, nullable=true)
     */
    protected $messageClass;

    /**
     * @ORM\Column(length=100)
     */
    protected $sender;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $recipients;

    /**
     * The API request JSON payload.
     *
     * @ORM\Column(type="text")
     */
    protected $requestPayload;

    /**
     * The successful API response JSON payload.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $responsePayload;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deliveredAt;

    public function __construct(string $messageClass, string $sender, array $recipients, string $requestPayload)
    {
        $this->messageClass = $messageClass;
        $this->sender = $sender;
        $this->recipients = $recipients;
        $this->requestPayload = base64_encode($requestPayload);
    }

    public function __toString(): string
    {
        return $this->messageClass.' de '.$this->sender.' à '.count($this->recipients).' destinataires';
    }

    public function getEnglishLog(): string
    {
        return $this->messageClass.' from '.$this->sender.' to '.count($this->recipients).' recipients';
    }

    public static function createFromMessage(Message $message, $requestPayload): self
    {
        $recipients = [];

        foreach ($message->getRecipients() as $recipient) {
            $recipients[] = $recipient->getFullName().' <'.$recipient->getEmailAddress().'>';
        }

        return new static(
            $message->getUuid(),
            str_replace('AppBundle\\Mailer\\Message\\', '', get_class($message)),
            $message->getReplyTo() ?? 'EnMarche',
            $recipients,
            $requestPayload
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
        return implode("\n", $this->recipients);
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
        return $this->responsePayload !== null;
    }

    public function getDeliveredAt(): ?\DateTime
    {
        return $this->deliveredAt;
    }
}
