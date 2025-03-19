<?php

namespace App\Entity\Renaissance\Adhesion;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AdherentRequestReminder
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $date;

    #[ORM\Column(type: 'string', enumType: AdherentRequestReminderTypeEnum::class)]
    public AdherentRequestReminderTypeEnum $type;

    #[ORM\ManyToOne(targetEntity: AdherentRequest::class)]
    public AdherentRequest $adherentRequest;

    public function __construct(
        AdherentRequest $adherentRequest,
        AdherentRequestReminderTypeEnum $type,
    ) {
        $this->date = new \DateTime();
        $this->type = $type;
        $this->adherentRequest = $adherentRequest;
    }
}
