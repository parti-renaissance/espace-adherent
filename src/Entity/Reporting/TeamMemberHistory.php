<?php

namespace App\Entity\Reporting;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Team\Team;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(indexes={
 *     @ORM\Index(name="team_member_history_adherent_id_idx", columns="adherent_id"),
 *     @ORM\Index(name="team_member_history_administrator_id_idx", columns="administrator_id"),
 *     @ORM\Index(name="team_member_history_team_manager_id_idx", columns="team_manager_id"),
 *     @ORM\Index(name="team_member_history_date_idx", columns="date")
 * })
 * @ORM\Entity
 */
class TeamMemberHistory
{
    public const ACTION_ADD = 'add';
    public const ACTION_REMOVE = 'remove';

    public const ACTION_CHOICES = [
        self::ACTION_ADD,
        self::ACTION_REMOVE,
    ];

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Team
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Team\Team")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $team;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @var Administrator|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $administrator;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $teamManager;

    /**
     * @var string
     *
     * @ORM\Column(length=20)
     */
    private $action;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $date;

    private function __construct(
        Team $team,
        Adherent $adherent,
        string $action,
        ?Administrator $administrator = null,
        ?Adherent $teamManager = null
    ) {
        $this->team = $team;
        $this->adherent = $adherent;
        $this->administrator = $administrator;
        $this->teamManager = $teamManager;
        $this->action = $action;
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function getTeamManager(): ?Adherent
    {
        return $this->teamManager;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function isAdded(): bool
    {
        return self::ACTION_ADD === $this->action;
    }

    public function isRemoved(): bool
    {
        return self::ACTION_REMOVE === $this->action;
    }

    public static function createAdd(Team $team, Adherent $adherent, $user = null): self
    {
        if ($user instanceof Adherent) {
            return new self($team, $adherent, self::ACTION_ADD, null, $user);
        }

        return new self($team, $adherent, self::ACTION_ADD, $user);
    }

    public static function createRemove(Team $team, Adherent $adherent, $user): self
    {
        if ($user instanceof Adherent) {
            return new self($team, $adherent, self::ACTION_REMOVE, null, $user);
        }

        return new self($team, $adherent, self::ACTION_REMOVE, $user);
    }
}
