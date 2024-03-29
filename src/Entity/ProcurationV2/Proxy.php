<?php

namespace App\Entity\ProcurationV2;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\InZoneOfScopeFilter;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Procuration\V2\ProxyStatusEnum;
use App\Validator\Procuration\AssociatedSlots;
use App\Validator\Procuration\ExcludedAssociations;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="procuration_v2_proxies")
 * @ORM\Entity(repositoryClass="App\Repository\Procuration\ProxyRepository")
 *
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
 *     itemOperations={},
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
 * @ApiFilter(InZoneOfScopeFilter::class)
 * @ApiFilter(OrderFilter::class, properties={"createdAt"})
 * @ApiFilter(SearchFilter::class, properties={"status": "exact"})
 */
class Proxy extends AbstractProcuration
{
    /**
     * @ORM\Column(length=9, nullable=true)
     *
     * @Assert\Length(min=7, max=9)
     * @Assert\Regex(pattern="/^[0-9]+$/i")
     */
    public ?string $electorNumber = null;

    /**
     * @ORM\Column(type="smallint", options={"default": 1, "unsigned": true})
     *
     * @Assert\Range(min=1, max=2)
     *
     * @Groups({
     *     "procuration_matched_proxy",
     *     "procuration_proxy_list",
     * })
     */
    public int $slots = 1;

    /**
     * @ORM\Column(enumType=ProxyStatusEnum::class)
     *
     * @Groups({
     *     "procuration_matched_proxy",
     *     "procuration_proxy_list",
     * })
     */
    public ProxyStatusEnum $status = ProxyStatusEnum::PENDING;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProcurationV2\Request", mappedBy="proxy", cascade={"all"})
     *
     * @Groups({
     *     "procuration_matched_proxy",
     *     "procuration_proxy_list",
     * })
     */
    public Collection $requests;

    public function __construct(
        Round $round,
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
        ?string $clientIp = null,
        ?\DateTimeInterface $createdAt = null
    ) {
        parent::__construct(
            $round,
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
            $clientIp,
            $createdAt
        );

        $this->requests = new ArrayCollection();
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
        return $this->requests->count() < $this->slots;
    }
}
