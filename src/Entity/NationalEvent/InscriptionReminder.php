<?php

declare(strict_types=1);

namespace App\Entity\NationalEvent;

use App\Entity\EntityTimestampableTrait;
use App\NationalEvent\InscriptionReminderTypeEnum;
use App\Repository\NationalEvent\InscriptionReminderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionReminderRepository::class)]
#[ORM\Table('national_event_inscription_reminder')]
class InscriptionReminder
{
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne]
    public EventInscription $inscription;

    #[ORM\Column(enumType: InscriptionReminderTypeEnum::class)]
    public InscriptionReminderTypeEnum $type;

    public function __construct(EventInscription $inscription, InscriptionReminderTypeEnum $type)
    {
        $this->inscription = $inscription;
        $this->type = $type;
    }
}
