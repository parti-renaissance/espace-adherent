<?php

declare(strict_types=1);

namespace App\Entity\ProcurationV2;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Put;
use App\Validator\Procuration\ManualSlot;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Put(
            uriTemplate: '/proxy_slots/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            denormalizationContext: ['groups' => ['procuration_proxy_slot_write']]
        ),
    ],
    routePrefix: '/v3/procuration',
    normalizationContext: ['groups' => ['procuration_proxy_slot_read']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'procurations')"
)]
#[ManualSlot]
#[ORM\Entity]
#[ORM\Table(name: 'procuration_v2_proxy_slot')]
class ProxySlot extends AbstractSlot
{
    #[Groups(['procuration_proxy_slot_read'])]
    #[ORM\JoinColumn(nullable: false,
        onDelete: 'CASCADE')]
    #[ORM\ManyToOne(inversedBy: 'proxySlots', targetEntity: Proxy::class)]
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
}
