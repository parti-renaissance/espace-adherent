<?php

namespace App\Entity\ProcurationV2;

use App\Entity\Adherent;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Procuration\V2\SlotActionStatusEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Index(columns: ['manual'])]
#[ORM\MappedSuperclass]
abstract class AbstractSlot implements EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use OrderedActionsTrait;

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'procuration_proxy_slot_read', 'procuration_request_slot_read'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Round::class, fetch: 'EXTRA_LAZY')]
    public Round $round;

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'procuration_proxy_slot_read', 'procuration_request_slot_read', 'procuration_request_slot_write', 'procuration_proxy_slot_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $manual = false;

    public function __construct(
        Round $round,
        ?UuidInterface $uuid = null,
    ) {
        $this->round = $round;
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->actions = new ArrayCollection();
    }

    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list', 'procuration_update_status', 'procuration_proxy_slot_read', 'procuration_request_slot_read', 'procuration_request_read', 'procuration_request_list'])]
    public function getMatchedAt(): ?\DateTimeInterface
    {
        foreach ($this->getOrderedActions() as $action) {
            if (SlotActionStatusEnum::MATCH === $action->status) {
                return $action->date;
            }
        }

        return null;
    }

    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list', 'procuration_update_status', 'procuration_proxy_slot_read', 'procuration_request_slot_read', 'procuration_request_read', 'procuration_request_list'])]
    public function getMatcher(): ?Adherent
    {
        foreach ($this->getOrderedActions() as $action) {
            if (SlotActionStatusEnum::MATCH === $action->status) {
                return $action->author;
            }
        }

        return null;
    }
}
