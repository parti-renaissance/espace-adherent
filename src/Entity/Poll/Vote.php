<?php

declare(strict_types=1);

namespace App\Entity\Poll;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Poll\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\Table(name: 'poll_vote')]
class Vote
{
    use EntityTimestampableTrait;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    protected $id;

    /**
     * @var Choice
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Choice::class, inversedBy: 'votes')]
    private $choice;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $adherent;

    /**
     * @var Device|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Device::class)]
    private $device;

    public function __construct(Choice $choice, ?Adherent $adherent = null, ?Device $device = null)
    {
        $this->choice = $choice;
        $this->adherent = $adherent;
        $this->device = $device;
    }

    public static function createForAdherent(Choice $choice, Adherent $adherent): self
    {
        return new self($choice, $adherent);
    }

    public static function createForDevice(Choice $choice, Device $device): self
    {
        return new self($choice, null, $device);
    }

    public static function createForAnonymous(Choice $choice): self
    {
        return new self($choice);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChoice(): Choice
    {
        return $this->choice;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }
}
