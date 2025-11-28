<?php

declare(strict_types=1);

namespace App\Entity\ProcurationV2;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Patch;
use App\Api\Filter\OrTextSearchFilter;
use App\Api\Filter\ProcurationZoneFilter;
use App\Controller\Api\Procuration\GetMatchedProxiesController;
use App\Controller\Api\Procuration\MatchRequestWithProxyController;
use App\Controller\Api\Procuration\UnmatchRequestAndProxyController;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Procuration\V2\RequestStatusEnum;
use App\Repository\Procuration\RequestRepository;
use App\Validator\Procuration\ExcludedAssociations;
use App\Validator\Procuration\ManualAssociations;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: OrderFilter::class, properties: ['createdAt'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['status' => 'exact'])]
#[ApiFilter(filterClass: OrTextSearchFilter::class, properties: ['firstNames' => 'lastName', 'lastName' => 'firstNames', 'email' => 'email', 'voteZone.name' => 'voteZone.name', 'votePlace.name' => 'votePlace.name'])]
#[ApiFilter(filterClass: ProcurationZoneFilter::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/requests/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['procuration_request_read'], 'enable_tag_translator' => true]
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/requests/{uuid}/match',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: MatchRequestWithProxyController::class,
            deserialize: false
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/requests/{uuid}/unmatch',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: UnmatchRequestAndProxyController::class,
            deserialize: false
        ),
        new Patch(
            uriTemplate: '/requests/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['procuration_update_status']],
            denormalizationContext: ['groups' => ['procuration_update_status']],
            validationContext: ['groups' => ['procuration_update_status']]
        ),
        new GetCollection(normalizationContext: ['groups' => ['procuration_request_list'], 'enable_tag_translator' => true]),
        new GetCollection(
            uriTemplate: '/requests/{uuid}/proxies',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: GetMatchedProxiesController::class,
            normalizationContext: ['groups' => ['procuration_matched_proxy'], 'enable_tag_translator' => true]
        ),
    ],
    routePrefix: '/v3/procuration',
    normalizationContext: ['groups' => ['procuration_request_list']],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 50,
    paginationMaximumItemsPerPage: 100,
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'procurations')"
)]
#[ExcludedAssociations]
#[ManualAssociations]
#[ORM\Entity(repositoryClass: RequestRepository::class)]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['created_at'])]
#[ORM\Table(name: 'procuration_v2_requests')]
class Request extends AbstractProcuration
{
    #[Assert\Choice(callback: [RequestStatusEnum::class,
        'getAvailableStatuses'], groups: ['procuration_update_status'])]
    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_update_status', 'procuration_proxy_slot_read', 'procuration_request_slot_read'])]
    #[ORM\Column(enumType: RequestStatusEnum::class)]
    public RequestStatusEnum $status = RequestStatusEnum::PENDING;

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list'])]
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $fromFrance;

    /**
     * @var Collection|RequestSlot[]
     */
    #[ORM\OneToMany(mappedBy: 'request', targetEntity: RequestSlot::class, cascade: ['all'], fetch: 'EXTRA_LAZY')]
    public Collection $requestSlots;

    /**
     * @var MatchingHistory[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'request', targetEntity: MatchingHistory::class)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    public Collection $matchingHistories;

    /**
     * @var RequestAction[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'request', targetEntity: RequestAction::class, cascade: ['all'])]
    #[ORM\OrderBy(['date' => 'DESC'])]
    public Collection $actions;

    public function __construct(
        UuidInterface $uuid,
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
        ?\DateTimeInterface $createdAt = null,
    ) {
        parent::__construct(
            $uuid,
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
        $this->actions = new ArrayCollection();
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

    public function isExcluded(): bool
    {
        return RequestStatusEnum::EXCLUDED === $this->status;
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
            if (!$requestSlot->proxySlot && !$requestSlot->manual) {
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

    public function hasManualSlot(): bool
    {
        foreach ($this->requestSlots as $requestSlot) {
            if ($requestSlot->manual) {
                return true;
            }
        }

        return false;
    }

    #[Groups(['procuration_request_read', 'procuration_request_list'])]
    #[SerializedName('request_slots')]
    public function getOrderedSlots(): array
    {
        $slots = $this->requestSlots->toArray();

        uasort($slots, fn (RequestSlot $a, RequestSlot $b) => $a->round->date <=> $b->round->date);

        return array_values($slots);
    }

    public function markAsDuplicate(?string $detail): void
    {
        $this->status = RequestStatusEnum::DUPLICATE;
        $this->statusDetail = $detail;
    }
}
