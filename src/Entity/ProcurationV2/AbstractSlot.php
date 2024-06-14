<?php

namespace App\Entity\ProcurationV2;

use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
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

    public function __construct(
        Round $round
    ) {
        $this->uuid = Uuid::uuid4();
        $this->round = $round;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }
}
