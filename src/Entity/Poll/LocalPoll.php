<?php

namespace App\Entity\Poll;

use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Poll\LocalPollRepository")
 */
class LocalPoll extends Poll implements AuthoredInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\NotBlank
     */
    private $author;

    /**
     * @var Zone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Assert\NotBlank
     */
    private $zone;

    public function __construct(
        Adherent $author,
        UuidInterface $uuid = null,
        string $question = null,
        \DateTimeInterface $finishAt = null,
        Zone $zone = null,
        bool $published = false
    ) {
        parent::__construct($uuid, $question, $finishAt, $published);

        $this->author = $author;
        $this->zone = $zone;

        $this->addChoice(new Choice(Choice::YES));
        $this->addChoice(new Choice(Choice::NO));
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author = null): void
    {
        $this->author = $author;
    }

    /**
     * @SymfonySerializer\Groups({"poll_read"})
     */
    public function getType(): string
    {
        return PollTypeEnum::LOCAL;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(Zone $zone): void
    {
        $this->zone = $zone;
    }
}
