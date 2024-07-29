<?php

namespace App\Entity\ElectedRepresentative\Reporting;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\UserListDefinition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'elected_representative_user_list_definition_history')]
class UserListDefinitionHistory
{
    private const ACTION_ADD = 'add';
    private const ACTION_REMOVE = 'remove';

    public const ACTION_CHOICES = [
        self::ACTION_ADD,
        self::ACTION_REMOVE,
    ];

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(length: 20)]
    private $action;

    /**
     * @var ElectedRepresentative
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectedRepresentative::class)]
    private $electedRepresentative;

    /**
     * @var UserListDefinition
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: UserListDefinition::class)]
    private $userListDefinition;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $adherent;

    /**
     * @var Administrator|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $administrator;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private $date;

    private function __construct(
        string $action,
        ElectedRepresentative $electedRepresentative,
        UserListDefinition $userListDefinition,
        UserInterface $user
    ) {
        if (!$user instanceof Adherent && !$user instanceof Administrator) {
            throw new \InvalidArgumentException(\sprintf('User must be an instance of "%s" or "%s".', Adherent::class, Administrator::class));
        }

        $this->action = $action;
        $this->electedRepresentative = $electedRepresentative;
        $this->userListDefinition = $userListDefinition;
        $this->date = new \DateTimeImmutable();

        if ($user instanceof Adherent) {
            $this->adherent = $user;
        } elseif ($user instanceof Administrator) {
            $this->administrator = $user;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getElectedRepresentative(): ElectedRepresentative
    {
        return $this->electedRepresentative;
    }

    public function getUserListDefinition(): UserListDefinition
    {
        return $this->userListDefinition;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public static function createAdd(
        UserInterface $user,
        ElectedRepresentative $electedRepresentative,
        UserListDefinition $userListDefinition
    ): self {
        return new self(self::ACTION_ADD, $electedRepresentative, $userListDefinition, $user);
    }

    public static function createRemove(
        UserInterface $user,
        ElectedRepresentative $electedRepresentative,
        UserListDefinition $userListDefinition
    ): self {
        return new self(self::ACTION_REMOVE, $electedRepresentative, $userListDefinition, $user);
    }
}
