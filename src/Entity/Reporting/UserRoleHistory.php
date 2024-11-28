<?php

namespace App\Entity\Reporting;

use App\Entity\Adherent;
use App\Entity\Administrator;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRoleHistoryRepository::class)]
#[ORM\Table(name: 'user_role_history')]
class UserRoleHistory
{
    private const ACTION_ADD = 'add';
    private const ACTION_REMOVE = 'remove';

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public Adherent $user;

    #[ORM\Column]
    public string $role;

    #[ORM\Column]
    public string $action;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeInterface $date;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    public ?Administrator $adminAuthor;

    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    #[ORM\ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?Adherent $userAuthor;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeInterface $telegramNotifiedAt = null;

    public function __construct(
        Adherent $user,
        string $action,
        string $role,
        ?Administrator $adminAuthor = null,
        ?Adherent $userAuthor = null,
    ) {
        $this->user = $user;
        $this->action = $action;
        $this->role = $role;
        $this->adminAuthor = $adminAuthor;
        $this->userAuthor = $userAuthor;
        $this->date = new \DateTimeImmutable();
    }
}
