<?php

namespace App\Entity\Poll;

use App\Entity\Administrator;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\Entity
 */
class NationalPoll extends Poll
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $administrator;

    public function __construct(
        Administrator $administrator = null,
        UuidInterface $uuid = null,
        string $question = null,
        \DateTimeInterface $finishAt = null
    ) {
        parent::__construct($uuid, $question, $finishAt, true);

        $this->administrator = $administrator;
    }

    public function setAdministrator(Administrator $administrator): void
    {
        $this->administrator = $administrator;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    /**
     * @SymfonySerializer\Groups({"poll_read"})
     */
    public function getType(): string
    {
        return PollTypeEnum::NATIONAL;
    }
}
