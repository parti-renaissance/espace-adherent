<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use App\AppSession\SystemEnum;
use App\Entity\Adherent;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(fields: ['message', 'source'])]
#[ORM\UniqueConstraint(fields: ['adherent', 'message', 'source'])]
class AdherentMessageReach
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne]
    public AdherentMessage $message;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne]
    public ?Adherent $adherent = null;

    #[ORM\Column]
    public string $source;

    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $date;

    public function __construct(AdherentMessage $message, Adherent $adherent, string $source, ?\DateTimeInterface $date = null)
    {
        $this->message = $message;
        $this->adherent = $adherent;
        $this->source = $source;
        $this->date = $date ?? new \DateTimeImmutable();
    }

    public static function createPush(AdherentMessage $adherentMessage, Adherent $adherent, \DateTimeInterface $date, ?SystemEnum $system): self
    {
        return new self($adherentMessage, $adherent, $system ? 'push:'.$system->value : 'push', $date);
    }

    public static function createApp(AdherentMessage $adherentMessage, Adherent $adherent, \DateTimeInterface $date): self
    {
        return new self($adherentMessage, $adherent, 'app', $date);
    }
}
