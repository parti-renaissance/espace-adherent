<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Mailer\Message\Message;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EmailLogRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class EmailLog
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column
     */
    private $sender;

    /**
     * @ORM\Column
     */
    private $subject;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\Column(type="integer")
     */
    private $recipientsNumber = 0;

    public function __construct(UuidInterface $uuid, string $subject, string $sender, string $body, int $recipientsNumber)
    {
        $this->uuid = $uuid;
        $this->subject = $subject;
        $this->sender = $sender;
        $this->body = $body;
        $this->recipientsNumber = $recipientsNumber;
    }

    public static function createFromMessage(Message $message): self
    {
        return new static(
            $message->getUuid(),
            $message->getSubject(),
            $message->getReplyTo(),
            array_key_exists('target_message', $message->getVars()) ? $message->getVars()['target_message'] : '',
            \count($message->getRecipients())
        );
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    public function getRecipientsNumber(): int
    {
        return $this->recipientsNumber;
    }

    public function setRecipientsNumber($recipientsNumber): void
    {
        $this->recipientsNumber = $recipientsNumber;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody($body): void
    {
        $this->body = $body;
    }
}
