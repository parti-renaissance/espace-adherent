<?php

namespace App\Entity\ProcurationV2;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={
 *         "routePrefix": "/v3/procuration",
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'procurations')",
 *         "denormalization_context": {"groups": {"proxy_slot_update_status"}},
 *         "normalization_context": {"groups": {"proxy_slot_read"}},
 *     },
 *     itemOperations={
 *         "put": {
 *             "path": "/proxy_slots/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "_api_respond": false,
 *         },
 *     },
 *     collectionOperations={},
 * )
 */
#[ORM\Table(name: 'procuration_v2_proxy_slot')]
#[ORM\Entity]
class ProxySlot extends AbstractSlot
{
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

    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list'])]
    public function getRequest(): ?Request
    {
        return $this->requestSlot?->request;
    }
}
