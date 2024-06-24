<?php

namespace App\Entity\ProcurationV2;

use App\Procuration\V2\SlotActionStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Table(name: 'procuration_v2_request_slot_action')]
#[ORM\Entity]
class RequestSlotAction extends AbstractSlotAction
{
    #[ORM\ManyToOne(targetEntity: RequestSlot::class, inversedBy: 'actions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public RequestSlot $requestSlot;

    public function __construct(UuidInterface $uuid, \DateTimeInterface $date, SlotActionStatusEnum $status, RequestSlot $requestSlot)
    {
        parent::__construct($uuid, $date, $status);

        $this->requestSlot = $requestSlot;
    }

    private static function create(SlotActionStatusEnum $status, RequestSlot $requestSlot): self
    {
        return new self(
            Uuid::uuid4(),
            new \DateTime(),
            $status,
            $requestSlot
        );
    }

    public static function createMatch(RequestSlot $requestSlot): self
    {
        return self::create(SlotActionStatusEnum::MATCH, $requestSlot);
    }

    public static function createUnmatch(RequestSlot $requestSlot): self
    {
        return self::create(SlotActionStatusEnum::UNMATCH, $requestSlot);
    }

    public static function createStatusUpdate(RequestSlot $requestSlot): self
    {
        return self::create(SlotActionStatusEnum::STATUS_UPDATE, $requestSlot);
    }
}
