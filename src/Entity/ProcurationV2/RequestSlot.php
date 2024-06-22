<?php

namespace App\Entity\ProcurationV2;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Procuration\RequestSlotRepository;
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

    /**
     * @var RequestSlotAction[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'requestSlot', targetEntity: RequestSlotAction::class, cascade: ['all'])]
    #[ORM\OrderBy(['date' => 'DESC'])]
    public Collection $actions;

    public function __construct(Round $round, Request $request, ?UuidInterface $uuid = null)
    {
        parent::__construct($round, $uuid);

        $this->request = $request;
        $this->actions = new ArrayCollection();
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_request_slot_read'])]
    public function getProxy(): ?Proxy
    {
        return $this->proxySlot?->proxy;
    }

    public function match(ProxySlot $proxySlot): void
    {
        $this->proxySlot = $proxySlot;
    }

    public function unmatch(): void
    {
        $this->proxySlot = null;
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'procuration_proxy_slot_read', 'procuration_request_slot_read'])]
    #[SerializedName('actions')]
    public function getOrderedActions(int $limit = 3): array
    {
        $actions = $this->actions->toArray();

        uasort($actions, fn (RequestSlotAction $a, RequestSlotAction $b) => $b->date <=> $a->date);

        return \array_slice(array_values($actions), 0, $limit);
    }
}
