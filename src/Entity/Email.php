<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Mailer\Message\Message;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(
 *     name="emails",
 *     indexes={
 *         @ORM\Index(name="emails_app_idx", columns="app"),
 *         @ORM\Index(name="emails_message_class_idx", columns="message_class"),
 *         @ORM\Index(name="emails_sender_idx", columns="sender")
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EmailRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Email
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * The app from where the message comes from
     *
     * @var string
     *
     * @ORM\Column(length=32)
     */
    private $app;

    /**
     * The message class namespace.
     *
     * @ORM\Column(length=55, nullable=true)
     */
    private $messageClass;

    /**
     * @ORM\Column(length=100)
     */
    private $sender;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $recipients;

    /**
     * The API request JSON payload.
     *
     * @ORM\Column(type="text")
     */
    private $requestPayload;

    /**
     * The successful API response JSON payload.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $responsePayload;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deliveredAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $lastFailedMessage;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastFailedDate;

    public function __construct(UuidInterface $uuid, string $messageClass, string $sender, array $recipients, string $requestPayload, string $app)
    {
        $this->uuid = $uuid;
        $this->messageClass = $messageClass;
        $this->sender = $sender;
        $this->recipients = $recipients;
        $this->requestPayload = base64_encode($requestPayload);
        $this->app = $app;
    }

    public function __toString(): string
    {
        return $this->messageClass.' de '.$this->sender.' à '.\count($this->recipients).' destinataires';
    }

    public function getEnglishLog(): string
    {
        return $this->messageClass.' from '.$this->sender.' to '.\count($this->recipients).' recipients';
    }

    public static function createFromMessage(Message $message, $requestPayload): self
    {
        $recipients = [];

        foreach ($message->getRecipients() as $recipient) {
            $recipients[] = $recipient->getFullName().' <'.$recipient->getEmailAddress().'>';
        }

        return new static(
            $message->getUuid(),
            str_replace('AppBundle\\Mailer\\Message\\', '', \get_class($message)),
            $message->getReplyTo() ?? 'EnMarche',
            $recipients,
            $requestPayload,
            $message->getApp()
        );
    }

    public function delivered(?string $responsePayload): void
    {
        $this->setResponsePayload($responsePayload);
        $this->deliveredAt = new \DateTime();
    }

    public function failed(?string $errorMessage, ?string $responsePayload): void
    {
        $this->lastFailedMessage = $errorMessage;
        $this->lastFailedDate = new \DateTime();
        $this->setResponsePayload($responsePayload);
    }

    public function getMessageClass(): string
    {
        return $this->messageClass;
    }

    public function getApp(): string
    {
        return $this->app;
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

    private function setResponsePayload(?string $responsePayload): void
    {
        $this->responsePayload = base64_encode($responsePayload);
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

    public function getLastFailedDate(): ?\DateTime
    {
        return $this->lastFailedDate;
    }

    public function getLastFailedMessage(): ?string
    {
        return $this->lastFailedMessage;
    }
}
