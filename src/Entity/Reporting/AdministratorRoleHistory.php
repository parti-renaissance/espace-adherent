<?php

declare(strict_types=1);

namespace App\Entity\Reporting;

use App\Entity\Administrator;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'administrator_role_history')]
class AdministratorRoleHistory
{
    private const ACTION_ADD = 'add';
    private const ACTION_REMOVE = 'remove';

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private Administrator $administrator;

    #[ORM\Column]
    private string $role;

    #[ORM\Column]
    private string $action;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $date;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private Administrator $author;

    private function __construct(
        Administrator $administrator,
        string $role,
        string $action,
        Administrator $author,
    ) {
        $this->administrator = $administrator;
        $this->role = $role;
        $this->action = $action;
        $this->author = $author;
        $this->date = new \DateTimeImmutable();
    }

    public static function createAdd(
        Administrator $administrator,
        string $role,
        Administrator $author,
    ): self {
        return new self($administrator, $role, self::ACTION_ADD, $author);
    }

    public static function createRemove(
        Administrator $administrator,
        string $role,
        Administrator $author,
    ): self {
        return new self($administrator, $role, self::ACTION_REMOVE, $author);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getAuthor(): Administrator
    {
        return $this->author;
    }
}
