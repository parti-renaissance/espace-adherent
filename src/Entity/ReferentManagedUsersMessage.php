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
    private $includeSupervisors;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $queryZone;

    /**
     * @ORM\Column(type="text")
     */
    private $queryAreaCode;

    /**
     * @ORM\Column(type="text")
     */
    private $queryId;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $interests;

    /**
     * @ORM\Column(length=6, nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ageMinimum;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ageMaximum;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $includeCP;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $registeredFrom;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $registeredTo;

    public function __construct(
        UuidInterface $uuid,
        Adherent $from,
        string $subject,
        string $content,
        bool $includeAdherentsNoCommittee,
        bool $includeAdherentsInCommittee,
        bool $includeHosts,
        bool $includeSupervisors,
        string $firstName,
        string $lastName,
        string $queryZone,
        string $queryAreaCode,
        string $queryId,
        string $gender,
        int $ageMinimum,
        int $ageMaximum,
        bool $includeCP,
        array $interests = [],
        int $offset = 0,
        \DateTimeInterface $registeredFrom = null,
        \DateTimeInterface $registeredTo = null
    ) {
        $this->uuid = $uuid;
        $this->from = $from;
        $this->subject = $subject;
        $this->content = $content;
        $this->includeAdherentsNoCommittee = $includeAdherentsNoCommittee;
        $this->includeAdherentsInCommittee = $includeAdherentsInCommittee;
        $this->includeHosts = $includeHosts;
        $this->includeSupervisors = $includeSupervisors;
        $this->queryZone = $queryZone;
        $this->queryAreaCode = $queryAreaCode;
        $this->queryId = $queryId;
        $this->offset = $offset;
        $this->interests = $interests;
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->ageMinimum = $ageMinimum;
        $this->ageMaximum = $ageMaximum;
        $this->includeCP = $includeCP;
        $this->registeredFrom = $registeredFrom;
        $this->registeredTo = $registeredTo;
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
            $message->getFilter()->getQueryFirstName(),
            $message->getFilter()->getQueryLastName(),
            $message->getFilter()->getQueryZone(),
            $message->getFilter()->getQueryAreaCode(),
            $message->getFilter()->getQueryId(),
            $message->getFilter()->getQueryGender(),
            $message->getFilter()->getQueryAgeMinimum(),
            $message->getFilter()->getQueryAgeMaximum(),
            $message->getFilter()->includeCitizenProject(),
            $message->getFilter()->getQueryInterests(),
            0, // offset
            $message->getFilter()->getQueryRegisteredFrom(),
            $message->getFilter()->getQueryRegisteredTo()
        );
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

    public function includeSupervisors(): bool
    {
        return $this->includeSupervisors;
    }

    public function getQueryZone(): ?string
    {
        return $this->queryZone;
    }

    public function getQueryAreaCode(): string
    {
        return $this->queryAreaCode;
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getAgeMinimum(): ?int
    {
        return $this->ageMinimum;
    }

    public function setAgeMinimum(?int $ageMinimum): void
    {
        $this->ageMinimum = $ageMinimum;
    }

    public function getAgeMaximum(): ?int
    {
        return $this->ageMaximum;
    }

    public function setAgeMaximum(?int $ageMaximum): void
    {
        $this->ageMaximum = $ageMaximum;
    }

    public function includeCitizenProject(): bool
    {
        return $this->includeCP;
    }

    public function getRegisteredFrom(): ?\DateTimeInterface
    {
        return $this->registeredFrom;
    }

    public function getRegisteredTo(): ?\DateTimeInterface
    {
        return $this->registeredTo;
    }
}
