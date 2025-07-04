<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait AuthorInstanceTrait
{
    use AuthoredTrait;

    #[Groups(['action_read', 'action_read_list', 'event_read', 'event_list_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $authorScope = null;

    #[Groups(['action_read', 'action_read_list', 'event_read', 'event_list_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $authorRole = null;

    #[Groups(['action_read', 'action_read_list', 'event_read', 'event_list_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $authorInstance = null;

    #[Groups(['action_read', 'action_read_list', 'event_read', 'event_list_read'])]
    #[ORM\Column(nullable: true)]
    private ?string $authorZone = null;

    #[Groups(['action_read', 'action_read_list', 'event_read', 'event_list_read'])]
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $authorTheme = null;

    public function getAuthorScope(): ?string
    {
        return $this->authorScope;
    }

    public function setAuthorScope(?string $authorScope): void
    {
        $this->authorScope = $authorScope;
    }

    public function getAuthorRole(): ?string
    {
        return $this->authorRole;
    }

    public function setAuthorRole(?string $authorRole): void
    {
        $this->authorRole = $authorRole;
    }

    public function getAuthorInstance(): ?string
    {
        return $this->authorInstance;
    }

    public function setAuthorInstance(?string $instance): void
    {
        $this->authorInstance = $instance;
    }

    public function getAuthorZone(): ?string
    {
        return $this->authorZone;
    }

    public function setAuthorZone(?string $zone): void
    {
        $this->authorZone = $zone;
    }

    public function getAuthorTheme(): ?array
    {
        return $this->authorTheme;
    }

    public function setAuthorTheme(?array $authorTheme): void
    {
        $this->authorTheme = $authorTheme;
    }
}
