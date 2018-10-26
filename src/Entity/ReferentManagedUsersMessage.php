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
class ReferentManagedUsersMessage extends ManagedUsersMessage
{
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
     * @ORM\Column(type="text")
     */
    private $queryAreaCode;

    /**
     * @ORM\Column(type="text")
     */
    private $queryCity;

    /**
     * @ORM\Column(type="text")
     */
    private $queryId;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $interests = [];

    /**
     * @var string|null
     *
     * @ORM\Column(length=6, nullable=true)
     */
    private $gender;

    public function __construct(
        UuidInterface $uuid,
        Adherent $from,
        string $subject,
        string $content,
        bool $includeAdherentsNoCommittee,
        bool $includeAdherentsInCommittee,
        bool $includeHosts,
        bool $includeSupevisors,
        string $queryAreaCode,
        string $queryCity,
        string $queryId,
        string $gender,
        array $interests = [],
        int $offset = 0
    ) {
        $this->uuid = $uuid;
        $this->from = $from;
        $this->subject = $subject;
        $this->content = $content;
        $this->includeAdherentsNoCommittee = $includeAdherentsNoCommittee;
        $this->includeAdherentsInCommittee = $includeAdherentsInCommittee;
        $this->includeHosts = $includeHosts;
        $this->includeSupevisors = $includeSupevisors;
        $this->queryAreaCode = $queryAreaCode;
        $this->queryCity = $queryCity;
        $this->queryId = $queryId;
        $this->offset = $offset;
        $this->interests = $interests;
        $this->gender = $gender;
    }

    public static function createFromMessage(ReferentMessage $message): self
    {
        return new self(
            $message->getUuid(),
            $message->getFrom(),
            $message->getSubject(),
            $message->getContent(),
            $message->getFilter()->includeAdherentsNoCommittee(),
            $message->getFilter()->includeAdherentsInCommittee(),
            $message->getFilter()->includeHosts(),
            $message->getFilter()->includeSupervisors(),
            $message->getFilter()->getQueryAreaCode(),
            $message->getFilter()->getQueryCity(),
            $message->getFilter()->getQueryId(),
            $message->getFilter()->getQueryGender(),
            $message->getFilter()->getQueryInterests()
        );
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

    public function getQueryAreaCode(): string
    {
        return $this->queryAreaCode;
    }

    public function getQueryCity(): string
    {
        return $this->queryCity;
    }

    public function getQueryId(): string
    {
        return $this->queryId;
    }

    public function getInterests(): array
    {
        return $this->interests;
    }

    public function setInterests(array $interests): void
    {
        $this->interests = $interests;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }
}
