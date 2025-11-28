<?php

declare(strict_types=1);

namespace App\Entity;

use App\History\AdministratorActionHistoryTypeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AdministratorActionHistory
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    public Administrator $administrator;

    #[ORM\Column(type: 'string', enumType: AdministratorActionHistoryTypeEnum::class)]
    public AdministratorActionHistoryTypeEnum $type;

    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $date;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $data = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeInterface $telegramNotifiedAt = null;

    public function __construct(
        Administrator $administrator,
        AdministratorActionHistoryTypeEnum $type,
        \DateTimeInterface $date,
        ?array $data = null,
    ) {
        $this->administrator = $administrator;
        $this->type = $type;
        $this->date = $date;
        $this->data = $data;
    }
}
