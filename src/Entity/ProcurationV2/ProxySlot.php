<?php

namespace App\Entity\ProcurationV2;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Validator\Procuration\ManualSlot;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

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

    /**
     * @var ProxySlotAction[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'proxySlot', targetEntity: ProxySlotAction::class, cascade: ['all'])]
    public Collection $actions;

    public function __construct(Round $round, Proxy $proxy, ?UuidInterface $uuid = null)
    {
        parent::__construct($round, $uuid);

        $this->proxy = $proxy;
        $this->actions = new ArrayCollection();
    }

    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list', 'procuration_proxy_slot_read'])]
    public function getRequest(): ?Request
    {
        return $this->requestSlot?->request;
    }

    public function match(RequestSlot $requestSlot): void
    {
        $this->requestSlot = $requestSlot;
    }

    public function unmatch(): void
    {
        $this->requestSlot = null;
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'procuration_proxy_slot_read', 'procuration_request_slot_read'])]
    #[SerializedName('actions')]
    public function getOrderedActions(int $limit = 3): array
    {
        $actions = $this->actions->toArray();

        uasort($actions, [AbstractAction::class, 'sort']);

        return \array_slice(array_values($actions), 0, $limit);
    }
}
