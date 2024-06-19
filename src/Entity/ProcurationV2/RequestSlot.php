<?php

namespace App\Entity\ProcurationV2;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Procuration\RequestSlotRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={
 *         "routePrefix": "/v3/procuration",
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'procurations')",
 *         "normalization_context": {"groups": {"procuration_request_slot_read"}},
 *     },
 *     itemOperations={
 *         "put": {
 *             "path": "/request_slots/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "denormalization_context": {"groups": {"procuration_request_slot_write"}},
 *         },
 *     },
 *     collectionOperations={},
 * )
 */
#[ORM\Table(name: 'procuration_v2_request_slot')]
#[ORM\Entity(repositoryClass: RequestSlotRepository::class)]
class RequestSlot extends AbstractSlot
{
    #[Groups(['procuration_request_slot_read'])]
    #[ORM\ManyToOne(inversedBy: 'requestSlots', targetEntity: Request::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public Request $request;

    #[ORM\OneToOne(inversedBy: 'requestSlot', targetEntity: ProxySlot::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?ProxySlot $proxySlot = null;

    public function __construct(Round $round, Request $request, ?UuidInterface $uuid = null)
    {
        parent::__construct($round, $uuid);

        $this->request = $request;
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_request_slot_read'])]
    public function getProxy(): ?Proxy
    {
        return $this->proxySlot?->proxy;
    }
}
