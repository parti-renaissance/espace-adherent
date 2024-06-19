<?php

namespace App\Entity\ProcurationV2;

use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\MappedSuperclass]
abstract class AbstractSlot
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Round::class)]
    public Round $round;

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'request_slot_update_status', 'proxy_slot_update_status'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $manual = false;

    public function __construct(
        Round $round,
        ?UuidInterface $uuid = null
    ) {
        $this->round = $round;
        $this->uuid = $uuid ?? Uuid::uuid4();
    }
}
