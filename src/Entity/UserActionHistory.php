<?php

namespace App\Entity;

use App\History\UserActionHistoryTypeEnum;
use App\Repository\UserActionHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserActionHistoryRepository::class)]
class UserActionHistory
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public Adherent $adherent;

    #[ORM\Column(type: 'string', enumType: UserActionHistoryTypeEnum::class)]
    public UserActionHistoryTypeEnum $type;

    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $date;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $data = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    public ?Administrator $impersonator = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeInterface $telegramNotifiedAt = null;

    public function __construct(
        Adherent $adherent,
        UserActionHistoryTypeEnum $type,
        \DateTimeInterface $date,
        ?array $data = null,
        ?Administrator $impersonator = null,
    ) {
        $this->adherent = $adherent;
        $this->type = $type;
        $this->date = $date;
        $this->data = $data;
        $this->impersonator = $impersonator;
    }

    public function isRoleType(): bool
    {
        return \in_array($this->type, [
            UserActionHistoryTypeEnum::ROLE_ADD,
            UserActionHistoryTypeEnum::ROLE_REMOVE,
        ], true);
    }

    public function isTeamMemberType(): bool
    {
        return \in_array($this->type, [
            UserActionHistoryTypeEnum::TEAM_MEMBER_ADD,
            UserActionHistoryTypeEnum::TEAM_MEMBER_EDIT,
            UserActionHistoryTypeEnum::TEAM_MEMBER_REMOVE,
        ], true);
    }
}
