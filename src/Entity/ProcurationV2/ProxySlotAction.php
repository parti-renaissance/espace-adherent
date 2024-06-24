<?php

namespace App\Entity\ProcurationV2;

use App\Procuration\V2\SlotActionStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Table(name: 'procuration_v2_proxy_slot_action')]
#[ORM\Entity]
class ProxySlotAction extends AbstractSlotAction
{
    #[ORM\ManyToOne(targetEntity: ProxySlot::class, inversedBy: 'actions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public ProxySlot $proxySlot;

    public function __construct(UuidInterface $uuid, \DateTimeInterface $date, SlotActionStatusEnum $status, ProxySlot $proxySlot)
    {
        parent::__construct($uuid, $date, $status);

        $this->proxySlot = $proxySlot;
    }

    private static function create(SlotActionStatusEnum $status, ProxySlot $proxySlot): self
    {
        return new self(
            Uuid::uuid4(),
            new \DateTime(),
            $status,
            $proxySlot
        );
    }

    public static function createMatch(ProxySlot $proxySlot): self
    {
        return self::create(SlotActionStatusEnum::MATCH, $proxySlot);
    }

    public static function createUnmatch(ProxySlot $proxySlot): self
    {
        return self::create(SlotActionStatusEnum::UNMATCH, $proxySlot);
    }

    public static function createStatusUpdate(ProxySlot $proxySlot): self
    {
        return self::create(SlotActionStatusEnum::STATUS_UPDATE, $proxySlot);
    }
}
