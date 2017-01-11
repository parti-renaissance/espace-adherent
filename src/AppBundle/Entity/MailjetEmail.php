<?php

namespace AppBundle\Entity;

use AppBundle\Mailjet\Message\MailjetMessage;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="mailjet_emails")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MailjetEmailRepository")
 */
class MailjetEmail
{
    use EntityIdentityTrait;

    /**
     * The Mailjet email subject.
     *
     * @ORM\Column(length=100)
     */
    private $subject;

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
     * The successful API response JSON payload.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $responsePayload;

    /**
     * Whether or not the message was successfully delivered.
     *
     * @ORM\Column(type="boolean")
     */
    private $delivered;

    public function __construct(
        UuidInterface $uuid,
        string $template,
        string $subject,
        string $requestPayload,
        string $responsePayload = null,
        string $messageClass = null,
        bool $delivered = false
    ) {
        $this->uuid = $uuid;
        $this->template = $template;
        $this->subject = $subject;
        $this->requestPayload = $requestPayload;
        $this->responsePayload = $responsePayload;
        $this->messageClass = $messageClass;
        $this->delivered = $delivered;
    }

    public static function createFromMessage(MailjetMessage $message, $requestPayload): self
    {
        return new self(
            $message->getUuid(),
            $message->getTemplate(),
            $message->getSubject(),
            $requestPayload,
            null,
            get_class($message)
        );
    }

    public function delivered(string $responsePayload = null)
    {
        $this->responsePayload = $responsePayload;
        $this->delivered = true;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getMessageClass()
    {
        return $this->messageClass;
    }

    public function getRequestPayload()
    {
        return $this->requestPayload;
    }

    public function getResponsePayload()
    {
        return $this->responsePayload;
    }

    public function isDelivered()
    {
        return $this->delivered;
    }
}
