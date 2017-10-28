<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Referent\ReferentMessage;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="referent_managed_users_message")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReferentManagedUsersMessageRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ReferentManagedUsersMessage
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(name="adherent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $from;

    /**
     * @ORM\Column
     */
    private $subject;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $includeNewsletter;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $includeAdherentsNoCommittee;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $includeAdherentsInCommittee;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $includeHosts;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $includeSupevisors;

    /**
     * @ORM\Column
     */
    private $queryPostalCode;

    /**
     * @ORM\Column
     */
    private $queryCity;

    /**
     * @ORM\Column
     */
    private $queryId;

    /**
     * @ORM\Column(type="bigint")
     */
    private $offset;

    public function __construct(
        UuidInterface $uuid,
        Adherent $from,
        string $subject,
        string $content,
        bool $includeNewsletter,
        bool $includeAdherentsNoCommittee,
        bool $includeAdherentsInCommittee,
        bool $includeHosts,
        bool $includeSupevisors,
        string $queryPostalCode,
        string $queryCity,
        string $queryId,
        int $offset = 0
    ) {
        $this->uuid = $uuid;
        $this->from = $from;
        $this->subject = $subject;
        $this->content = $content;
        $this->includeNewsletter = $includeNewsletter;
        $this->includeAdherentsNoCommittee = $includeAdherentsNoCommittee;
        $this->includeAdherentsInCommittee = $includeAdherentsInCommittee;
        $this->includeHosts = $includeHosts;
        $this->includeSupevisors = $includeSupevisors;
        $this->queryPostalCode = $queryPostalCode;
        $this->queryCity = $queryCity;
        $this->queryId = $queryId;
        $this->offset = $offset;
    }

    public static function createFromMessage(ReferentMessage $message): self
    {
        return new self(
            $message->getUuid(),
            $message->getFrom(),
            $message->getSubject(),
            $message->getContent(),
            $message->getFilter()->includeNewsletter(),
            $message->getFilter()->includeAdherentsNoCommittee(),
            $message->getFilter()->includeAdherentsInCommittee(),
            $message->getFilter()->includeHosts(),
            $message->getFilter()->includeSupervisors(),
            $message->getFilter()->getQueryPostalCode(),
            $message->getFilter()->getQueryCity(),
            $message->getFilter()->getQueryId()
        );
    }

    public function incrementOffset(int $offset): void
    {
        $this->offset += $offset;
    }

    public function getFrom(): Adherent
    {
        return $this->from;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function includeNewsletter(): bool
    {
        return $this->includeNewsletter;
    }

    public function includeAdherentsNoCommittee(): bool
    {
        return $this->includeAdherentsNoCommittee;
    }

    public function includeAdherentsInCommittee(): bool
    {
        return $this->includeAdherentsInCommittee;
    }

    public function includeHosts(): bool
    {
        return $this->includeHosts;
    }

    public function includeSupevisors(): bool
    {
        return $this->includeSupevisors;
    }

    public function getQueryPostalCode(): string
    {
        return $this->queryPostalCode;
    }

    public function getQueryCity(): string
    {
        return $this->queryCity;
    }

    public function getQueryId(): string
    {
        return $this->queryId;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
