<?php

namespace App\Entity;

use App\History\UserActionHistoryTypeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(columns: ['adherent_id'])]
#[ORM\Table]
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
    public ?Administrator $impersonificator = null;

    public function __construct(
        Adherent $adherent,
        UserActionHistoryTypeEnum $type,
        \DateTimeInterface $date,
        ?array $data = null,
        ?Administrator $impersonificator = null,
    ) {
        $this->adherent = $adherent;
        $this->type = $type;
        $this->date = $date;
        $this->data = $data;
        $this->impersonificator = $impersonificator;
    }
}
