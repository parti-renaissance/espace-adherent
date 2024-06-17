<?php

namespace App\Entity\ProcurationV2;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\OrTextSearchFilter;
use App\Entity\Adherent;
use App\Entity\Chatbot\Message;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Procuration\V2\ProxyStatusEnum;
use App\Repository\Procuration\ProxyRepository;
use App\Validator\Procuration\AssociatedSlots;
use App\Validator\Procuration\ExcludedAssociations;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssociatedSlots
 * @ExcludedAssociations
 *
 * @ApiResource(
 *     attributes={
 *         "routePrefix": "/v3/procuration",
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'procurations')",
 *         "pagination_client_items_per_page": true,
 *         "normalization_context": {
 *             "groups": {"procuration_proxy_list"},
 *         },
 *     },
 *     itemOperations={
 *         "update_status": {
 *             "method": "PATCH",
 *             "path": "/proxies/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "validation_groups": {"procuration_update_status"},
 *             "normalization_context": {
 *                 "groups": {"procuration_update_status"},
 *             },
 *             "denormalization_context": {
 *                 "groups": {"procuration_update_status"},
 *             },
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "normalization_context": {
 *                 "groups": {"procuration_proxy_list"},
 *                 "enable_tag_translator": true,
 *                 "datetime_format": "Y-m-d",
 *             },
 *         },
 *     },
 * )
 *
 * @ApiFilter(OrderFilter::class, properties={"createdAt"})
 * @ApiFilter(SearchFilter::class, properties={"status": "exact"})
 * @ApiFilter(OrTextSearchFilter::class, properties={"firstNames": "lastName", "lastName": "firstNames", "email": "email", "voteZone.name": "voteZone.name", "votePlace.name": "votePlace.name"})
 */
#[ORM\Table(name: 'procuration_v2_proxies')]
#[ORM\Entity(repositoryClass: ProxyRepository::class)]
class Proxy extends AbstractProcuration
{
    /**
     * @Assert\Length(min=7, max=9)
     * @Assert\Regex(pattern="/^[0-9]+$/i")
     */
    #[ORM\Column(length: 9, nullable: true)]
    public ?string $electorNumber = null;

    /**
     * @Assert\Expression(
     *     expression="value >= 1 and ((!this.isFDE() and value == 1) or (this.isFDE() and value <= 3))",
     *     message="procuration.proxy.slots.invalid"
     * )
     */
    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list'])]
    #[ORM\Column(type: 'smallint', options: ['default' => 1, 'unsigned' => true])]
    public int $slots = 1;

    /**
     * @Assert\Choice(callback={"App\Procuration\V2\ProxyStatusEnum", "getAvailableStatuses"}, groups={"procuration_update_status"})
     */
    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list', 'procuration_update_status'])]
    #[ORM\Column(enumType: ProxyStatusEnum::class)]
    public ProxyStatusEnum $status = ProxyStatusEnum::PENDING;

    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list'])]
    #[ORM\OneToMany(mappedBy: 'proxy', targetEntity: Request::class, cascade: ['all'])]
    public Collection $requests;

    /**
     * @var Collection|ProxySlot[]
     */
    #[ORM\OneToMany(mappedBy: 'proxy', targetEntity: ProxySlot::class, cascade: ['all'])]
    public Collection $proxySlots;

    /**
     * Ids of main and parents zones built from votePlace zone or from voteZone zone.
     * This field helps to improve matching DB query in ProxyRepository
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $zoneIds = null;

    public function __construct(
        array $rounds,
        string $email,
        string $gender,
        string $firstNames,
        string $lastName,
        \DateTimeInterface $birthdate,
        ?PhoneNumber $phone,
        PostAddress $postAddress,
        bool $distantVotePlace,
        Zone $voteZone,
        ?Zone $votePlace = null,
        ?string $customVotePlace = null,
        ?Adherent $adherent = null,
        bool $joinNewsletter = false,
        ?string $clientIp = null,
        ?\DateTimeInterface $createdAt = null
    ) {
        parent::__construct(
            $rounds,
            $email,
            $gender,
            $firstNames,
            $lastName,
            $birthdate,
            $phone,
            $postAddress,
            $distantVotePlace,
            $voteZone,
            $votePlace,
            $customVotePlace,
            $adherent,
            $joinNewsletter,
            $clientIp,
            $createdAt
        );

        $this->requests = new ArrayCollection();
        $this->proxySlots = new ArrayCollection();
    }

    public function hasRequest(Request $request): bool
    {
        return $this->requests->contains($request);
    }

    public function addRequest(Request $request): void
    {
        if (!$this->requests->contains($request)) {
            $request->proxy = $this;
            $this->requests->add($request);
        }
    }

    public function removeRequest(Request $request): void
    {
        $this->requests->removeElement($request);
        $request->proxy = null;
    }

    public function isPending(): bool
    {
        return ProxyStatusEnum::PENDING === $this->status;
    }

    public function isCompleted(): bool
    {
        return ProxyStatusEnum::COMPLETED === $this->status;
    }

    public function isExcluded(): bool
    {
        return ProxyStatusEnum::EXCLUDED === $this->status;
    }

    public function markAsPending(): void
    {
        $this->status = ProxyStatusEnum::PENDING;
    }

    public function markAsCompleted(): void
    {
        $this->status = ProxyStatusEnum::COMPLETED;
    }

    public function hasFreeSlot(): bool
    {
        foreach ($this->proxySlots as $proxySlot) {
            if (!$proxySlot->requestSlot) {
                return true;
            }
        }

        return false;
    }

    public function refreshZoneIds(): void
    {
        $this->zoneIds = array_map(
            fn (Zone $zone) => $zone->getId(),
            ($this->votePlace ?? $this->voteZone)->getWithParents()
        );
    }

    public function matchSlot(Round $round, Request $request): void
    {
        /** @var ProxySlot|null $proxySlot */
        $proxySlot = $this->proxySlots->filter(
            static function (ProxySlot $proxySlot) use ($round): bool {
                return $round === $proxySlot->round && null === $proxySlot->requestSlot;
            }
        )->first() ?? null;

        /** @var RequestSlot|null $requestSlot */
        $requestSlot = $request->requestSlots->filter(
            static function (RequestSlot $requestSlot) use ($round): bool {
                return $round === $requestSlot->round && null === $requestSlot->proxySlot;
            }
        )->first() ?? null;

        if ($proxySlot && $requestSlot) {
            $requestSlot->proxySlot = $proxySlot;
            $proxySlot->requestSlot = $requestSlot;
        }
    }

    public function unmatchSlot(Round $round, Request $request): void
    {
        /** @var ProxySlot|null $proxySlot */
        $proxySlot = $this->proxySlots->filter(
            function (ProxySlot $proxySlot) use ($round, $request): bool {
                return $round === $proxySlot->round && $request === $proxySlot->requestSlot?->request;
            }
        )->first() ?? null;

        if ($proxySlot) {
            $proxySlot->requestSlot = null;
        }

        /** @var RequestSlot|null $requestSlot */
        $requestSlot = $request->requestSlots->filter(
            function (RequestSlot $requestSlot) use ($round): bool {
                return $round === $requestSlot->round && $this === $requestSlot->proxySlot?->proxy;
            }
        )->first() ?? null;

        if ($requestSlot) {
            $requestSlot->proxySlot = null;
        }
    }

    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list'])]
    #[SerializedName('proxy_slots')]
    public function getOrderedProxySlots(): array
    {
        $slots = $this->proxySlots->toArray();

        uasort($slots, fn (ProxySlot $a, ProxySlot $b) => $a->round->date <=> $b->round->date);

        return array_values($slots);
    }
}
