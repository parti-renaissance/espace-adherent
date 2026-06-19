<?php

declare(strict_types=1);

namespace App\Entity\Pronostic;

use App\Entity\EntityTimestampableTrait;
use App\Pronostic\PronosticReminderTypeEnum;
use App\Repository\Pronostic\PronosticReminderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PronosticReminderRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_pronostic_reminder', columns: ['pronostic_id', 'type'])]
class PronosticReminder
{
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Pronostic::class)]
    public Pronostic $pronostic;

    #[ORM\Column(enumType: PronosticReminderTypeEnum::class)]
    public PronosticReminderTypeEnum $type;

    public function __construct(Pronostic $pronostic, PronosticReminderTypeEnum $type)
    {
        $this->pronostic = $pronostic;
        $this->type = $type;
    }
}
