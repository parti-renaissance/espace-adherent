<?php

declare(strict_types=1);

namespace App\Entity\Poll;

use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\Geo\Zone;
use App\Repository\Poll\LocalPollRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LocalPollRepository::class)]
class LocalPoll extends Poll implements AuthoredInterface
{
    #[Assert\NotBlank]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $author;

    /**
     * @var Zone|null
     */
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    private $zone;

    public function __construct(
        Adherent $author,
        ?UuidInterface $uuid = null,
        ?string $question = null,
        ?\DateTimeInterface $finishAt = null,
        ?Zone $zone = null,
        bool $published = false,
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

    public function setAuthor(?Adherent $author = null): void
    {
        $this->author = $author;
    }

    #[Groups(['poll_read'])]
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
