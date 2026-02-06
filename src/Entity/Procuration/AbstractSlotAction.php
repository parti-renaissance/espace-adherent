<?php

declare(strict_types=1);

namespace App\Entity\Procuration;

use App\Procuration\SlotActionStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\MappedSuperclass]
abstract class AbstractSlotAction extends AbstractAction
{
    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'procuration_proxy_slot_read', 'procuration_request_slot_read'])]
    #[ORM\Column(enumType: SlotActionStatusEnum::class)]
    public SlotActionStatusEnum $status;

    public function __construct(UuidInterface $uuid, \DateTimeInterface $date, SlotActionStatusEnum $status)
    {
        parent::__construct($uuid, $date);

        $this->status = $status;
    }
}
