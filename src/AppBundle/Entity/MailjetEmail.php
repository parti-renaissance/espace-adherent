<?php

namespace AppBundle\Entity;

use AppBundle\Mailjet\Message\MailjetMessage;
use AppBundle\ValueObject\SHA1;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(
 *   name="mailjet_emails",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="mailjet_email_uuid", columns="uuid")
 *   },
 *   indexes={
 *     @ORM\Index(name="mailjet_email_message_batch_uuid", columns="message_batch_uuid")
 *   }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MailjetEmailRepository")
 */
class MailjetEmail
{
    use EntityIdentityTrait;

    /**
     * The Mailjet message UUID.
     *
     * @ORM\Column(type="uuid")
     */
    private $messageBatchUuid;

    /**
     * The Mailjet email subject.
     *
     * @ORM\Column(length=100)
     */
    private $subject;

    /**
     * The Mailjet email recipient email address.
     *
     * @ORM\Column
     */
    private $recipient;

    /**
     * The Mailjet template ID.
     *
     * @ORM\Column(length=10)
     */
    private $template;

    /**
     * The Mailjet message class namespace.
     *
     * @ORM\Column(nullable=true)
     */
    private $messageClass;

    /**
     * The API request JSON payload.
     *
     * @ORM\Column(type="text")
     */
    private $requestPayload;

    /**
     * The request payload SHA1 checksum.
     *
     * @ORM\Column(length=40)
     */
    private $requestPayloadChecksum;

    /**
     * The successful API response JSON payload.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $responsePayload;

    /**
     * The response payload SHA1 checksum.
     *
     * @ORM\Column(length=40, nullable=true)
     */
    private $responsePayloadChecksum;

    /**
     * Whether or not the message was successfully delivered.
     *
     * @ORM\Column(type="boolean")
     */
    private $delivered;

    /**
     * The date and time when the email was sent.
     *
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime")
     */
    private $sentAt;

    public function __construct(
        UuidInterface $uuid,
        UuidInterface $messageBatchUuid,
        string $template,
        string $subject,
        string $recipient,
        string $requestPayload,
        string $responsePayload = null,
        string $messageClass = null,
        bool $delivered = false,
        string $sentAt = 'now',
        UuidInterface $batch = null
    ) {
        $this->uuid = $uuid;
        $this->messageBatchUuid = $messageBatchUuid;
        $this->template = $template;
        $this->subject = $subject;
        $this->recipient = $recipient;
        $this->setPayloads($requestPayload, $responsePayload);
        $this->messageClass = $messageClass;
        $this->delivered = $delivered;
        $this->sentAt = new \DateTimeImmutable($sentAt);
    }

    public static function createFromMessage(MailjetMessage $message, string $recipientEmailAddress, $requestPayload): self
    {
        $email = new self(
            Uuid::uuid4(),
            $message->getBatch(),
            $message->getTemplate(),
            $message->getSubject(),
            $recipientEmailAddress,
            $requestPayload,
            null,
            get_class($message)
        );

        return $email;
    }

    public function delivered(string $responsePayload = null)
    {
        if ($responsePayload) {
            $this->setResponsePayload($responsePayload);
        }

        $this->delivered = true;
    }

    public function getMessageBatchUuid(): UuidInterface
    {
        return $this->messageBatchUuid;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getMessageClass()
    {
        return $this->messageClass;
    }

    public function getRequestPayload(): string
    {
        return $this->requestPayload;
    }

    public function getResponsePayload()
    {
        return $this->responsePayload;
    }

    public function isDelivered(): bool
    {
        return $this->delivered;
    }

    private function setPayloads(string $requestPayload, string $responsePayload = null)
    {
        $this->requestPayload = $requestPayload;
        $this->requestPayloadChecksum = SHA1::hash($requestPayload)->getHash();

        if ($responsePayload) {
            $this->setResponsePayload($responsePayload);
        }
    }

    private function setResponsePayload(string $responsePayload)
    {
        $this->responsePayload = $responsePayload;
        $this->responsePayloadChecksum = SHA1::hash($responsePayload)->getHash();
    }
}
