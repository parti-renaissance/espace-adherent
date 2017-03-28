<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Mailjet\Message\MailjetMessage;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="mailjet_logs")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MailjetEmailRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class MailjetEmail
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * The Mailjet message class namespace.
     *
     * @ORM\Column(length=50, nullable=true)
     */
    private $messageClass;

    /**
     * @ORM\Column(length=100)
     */
    private $sender;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $recipients = [];

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

    public function __construct(UuidInterface $uuid, string $messageClass, string $sender, array $recipients, string $requestPayload)
    {
        $this->uuid = $uuid;
        $this->messageClass = $messageClass;
        $this->sender = $sender;
        $this->recipients = $recipients;
        $this->requestPayload = base64_encode($requestPayload);
    }

    public function __toString()
    {
        return $this->messageClass.' de '.$this->sender.' à '.count($this->recipients).' destinataires';
    }

    public static function createFromMessage(MailjetMessage $message, $requestPayload): self
    {
        $recipients = [];

        foreach ($message->getRecipients() as $recipient) {
            $recipients[] = $recipient->getFullName().' <'.$recipient->getEmailAddress().'>';
        }

        return new self(
            $message->getBatch(),
            str_replace('AppBundle\\Mailjet\\Message\\', '', get_class($message)),
            $message->getReplyTo() ?? 'EnMarche',
            $recipients,
            $requestPayload
        );
    }

    public function delivered(?string $responsePayload)
    {
        $this->responsePayload = base64_encode($responsePayload);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMessageClass()
    {
        return $this->messageClass;
    }

    public function getSender()
    {
        return $this->sender;
    }

    public function getRecipients()
    {
        return $this->recipients;
    }

    public function getRecipientsAsString()
    {
        return implode("\n", $this->recipients);
    }

    public function getRequestPayload()
    {
        return json_decode(base64_decode($this->requestPayload), true);
    }

    public function getRequestPayloadJson()
    {
        return base64_decode($this->requestPayload);
    }

    public function getResponsePayload()
    {
        return json_decode(base64_decode($this->responsePayload), true);
    }

    public function getResponsePayloadJson()
    {
        return base64_decode($this->responsePayload);
    }

    public function isDelivered(): bool
    {
        return $this->responsePayload !== null;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
