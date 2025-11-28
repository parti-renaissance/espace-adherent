<?php

declare(strict_types=1);

namespace App\Entity\Poll;

use App\Entity\Administrator;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class NationalPoll extends Poll
{
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $administrator;

    public function __construct(
        ?Administrator $administrator = null,
        ?UuidInterface $uuid = null,
        ?string $question = null,
        ?\DateTimeInterface $finishAt = null,
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

    #[Groups(['poll_read'])]
    public function getType(): string
    {
        return PollTypeEnum::NATIONAL;
    }
}
