<?php

declare(strict_types=1);

namespace App\Entity\ProcurationV2;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Put;
use App\Entity\Adherent;
use App\Repository\Procuration\RequestSlotRepository;
use App\Validator\Procuration\ManualSlot;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Put(
            uriTemplate: '/request_slots/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            denormalizationContext: ['groups' => ['procuration_request_slot_write']]
        ),
    ],
    routePrefix: '/v3/procuration',
    normalizationContext: ['groups' => ['procuration_request_slot_read']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'procurations')"
)]
#[ManualSlot]
#[ORM\Entity(repositoryClass: RequestSlotRepository::class)]
#[ORM\Table(name: 'procuration_v2_request_slot')]
class RequestSlot extends AbstractSlot
{
    #[Groups(['procuration_request_slot_read'])]
    #[ORM\JoinColumn(nullable: false,
        onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Request::class, fetch: 'EXTRA_LAZY', inversedBy: 'requestSlots')]
    public Request $request;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(inversedBy: 'requestSlot', targetEntity: ProxySlot::class)]
    public ?ProxySlot $proxySlot = null;

    /**
     * @var RequestSlotAction[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'requestSlot', targetEntity: RequestSlotAction::class, cascade: ['all'])]
    #[ORM\OrderBy(['date' => 'DESC'])]
    public Collection $actions;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $matchRemindedAt = null;

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

    public function isMatchReminded(): bool
    {
        return null !== $this->matchRemindedAt;
    }

    public function remindMatch(): void
    {
        $this->matchRemindedAt = new \DateTime();
    }

    public function getMatcher(): ?Adherent
    {
        /** @var RequestSlotAction|null $lastMatchAction */
        $lastMatchAction = null;

        foreach ($this->actions as $action) {
            if (
                $action->isMatchAction()
                && (
                    !$lastMatchAction
                    || $lastMatchAction->date < $action->date
                )
            ) {
                $lastMatchAction = $action;
            }
        }

        return $lastMatchAction?->author;
    }
}
