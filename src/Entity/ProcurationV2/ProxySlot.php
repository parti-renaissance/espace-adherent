<?php

namespace App\Entity\ProcurationV2;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Validator\Procuration\ManualSlot;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ManualSlot
 *
 * @ApiResource(
 *     attributes={
 *         "routePrefix": "/v3/procuration",
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'procurations')",
 *         "normalization_context": {"groups": {"procuration_proxy_slot_read"}},
 *     },
 *     itemOperations={
 *         "put": {
 *             "path": "/proxy_slots/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "denormalization_context": {"groups": {"procuration_proxy_slot_write"}},
 *         },
 *     },
 *     collectionOperations={},
 * )
 */
#[ORM\Table(name: 'procuration_v2_proxy_slot')]
#[ORM\Entity]
class ProxySlot extends AbstractSlot
{
    #[Groups(['procuration_proxy_slot_read'])]
    #[ORM\ManyToOne(inversedBy: 'proxySlots', targetEntity: Proxy::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public Proxy $proxy;

    #[ORM\OneToOne(mappedBy: 'proxySlot', targetEntity: RequestSlot::class)]
    public ?RequestSlot $requestSlot = null;

    public function __construct(Round $round, Proxy $proxy, ?UuidInterface $uuid = null)
    {
        parent::__construct($round, $uuid);

        $this->proxy = $proxy;
    }

    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list', 'procuration_proxy_slot_read'])]
    public function getRequest(): ?Request
    {
        return $this->requestSlot?->request;
    }
}
