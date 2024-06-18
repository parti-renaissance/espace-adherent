<?php

namespace App\Entity\ProcurationV2;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\OrTextSearchFilter;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Procuration\V2\RequestStatusEnum;
use App\Repository\Procuration\RequestRepository;
use App\Validator\Procuration\ManualAssociations;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ManualAssociations
 *
 * @ApiResource(
 *     attributes={
 *         "routePrefix": "/v3/procuration",
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'procurations')",
 *         "pagination_client_items_per_page": true,
 *         "normalization_context": {
 *             "groups": {"procuration_request_list"},
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/requests/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "normalization_context": {
 *                 "groups": {"procuration_request_read"},
 *                 "enable_tag_translator": true,
 *             },
 *         },
 *         "match": {
 *             "method": "POST",
 *             "path": "/requests/{uuid}/match",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Procuration\MatchRequestWithProxyController",
 *             "defaults": {"_api_receive": false},
 *         },
 *         "unmatch": {
 *             "method": "POST",
 *             "path": "/requests/{uuid}/unmatch",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Procuration\UnmatchRequestAndProxyController",
 *             "defaults": {"_api_receive": false},
 *         },
 *         "update_status": {
 *             "method": "PATCH",
 *             "path": "/requests/{uuid}",
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
 *                 "groups": {"procuration_request_list"},
 *                 "enable_tag_translator": true,
 *             },
 *         },
 *         "get_proxies": {
 *             "method": "GET",
 *             "path": "/requests/{uuid}/proxies",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Procuration\GetMatchedProxiesController",
 *             "normalization_context": {
 *                 "groups": {"procuration_matched_proxy"},
 *                 "enable_tag_translator": true,
 *             },
 *         },
 *     },
 * )
 *
 * @ApiFilter(OrderFilter::class, properties={"createdAt"})
 * @ApiFilter(SearchFilter::class, properties={"status": "exact"})
 * @ApiFilter(OrTextSearchFilter::class, properties={"firstNames": "lastName", "lastName": "firstNames", "email": "email", "voteZone.name": "voteZone.name", "votePlace.name": "votePlace.name"})
 */
#[ORM\Table(name: 'procuration_v2_requests')]
#[ORM\Entity(repositoryClass: RequestRepository::class)]
class Request extends AbstractProcuration
{
    /**
     * @Assert\Choice(callback={"App\Procuration\V2\RequestStatusEnum", "getAvailableStatuses"}, groups={"procuration_update_status"})
     */
    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_update_status'])]
    #[ORM\Column(enumType: RequestStatusEnum::class)]
    public RequestStatusEnum $status = RequestStatusEnum::PENDING;

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list'])]
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $fromFrance;

    /**
     * @var Collection|RequestSlot[]
     */
    #[ORM\OneToMany(mappedBy: 'request', targetEntity: RequestSlot::class, cascade: ['all'])]
    public Collection $requestSlots;

    /**
     * @var MatchingHistory[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'request', targetEntity: MatchingHistory::class)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $matchingHistories;

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
        bool $fromFrance = true,
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

        $this->fromFrance = $fromFrance;
        $this->matchingHistories = new ArrayCollection();
        $this->requestSlots = new ArrayCollection();
    }

    public function isPending(): bool
    {
        return RequestStatusEnum::PENDING === $this->status;
    }

    public function isCompleted(): bool
    {
        return RequestStatusEnum::COMPLETED === $this->status;
    }

    public function isManual(): bool
    {
        return RequestStatusEnum::MANUAL === $this->status;
    }

    public function markAsPending(): void
    {
        $this->status = RequestStatusEnum::PENDING;
    }

    public function markAsCompleted(): void
    {
        $this->status = RequestStatusEnum::COMPLETED;
    }

    public function hasFreeSlot(): bool
    {
        foreach ($this->requestSlots as $requestSlot) {
            if (!$requestSlot->proxySlot) {
                return true;
            }
        }

        return false;
    }

    public function hasMatchedSlot(): bool
    {
        foreach ($this->requestSlots as $requestSlot) {
            if ($requestSlot->proxySlot) {
                return true;
            }
        }

        return false;
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list_request', 'procuration_matched_proxy'])]
    public function getMatcher(): ?Adherent
    {
        /** @var MatchingHistory $last */
        if ($last = $this->matchingHistories->last()) {
            return $last->matcher;
        }

        return null;
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list_request', 'procuration_matched_proxy'])]
    public function getMatchedAt(): ?\DateTimeInterface
    {
        /** @var MatchingHistory $last */
        if ($last = $this->matchingHistories->last()) {
            return $last->createdAt;
        }

        return null;
    }

    #[Groups(['procuration_request_read', 'procuration_request_list'])]
    #[SerializedName('request_slots')]
    public function getOrderedRequestSlots(): array
    {
        $slots = $this->requestSlots->toArray();

        uasort($slots, fn (RequestSlot $a, RequestSlot $b) => $a->round->date <=> $b->round->date);

        return array_values($slots);
    }
}
