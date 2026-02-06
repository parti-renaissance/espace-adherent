<?php

declare(strict_types=1);

namespace App\Entity\Procuration;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Api\Filter\OrTextSearchFilter;
use App\Api\Filter\ProcurationZoneFilter;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Procuration\ProxyStatusEnum;
use App\Repository\Procuration\ProxyRepository;
use App\Validator\Procuration\ExcludedAssociations;
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
#[ApiFilter(filterClass: OrTextSearchFilter::class, properties: ['firstNames' => 'lastName', 'lastName' => 'firstNames', 'email' => 'email', 'voteZone.name' => 'votePlace.name'])]
#[ApiFilter(filterClass: ProcurationZoneFilter::class)]
#[ApiResource(
    operations: [
        new Patch(
            uriTemplate: '/proxies/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['procuration_update_status']],
            denormalizationContext: ['groups' => ['procuration_update_status']],
            validationContext: ['groups' => ['procuration_update_status']]
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['procuration_proxy_list'], 'enable_tag_translator' => true, 'datetime_format' => 'Y-m-d']
        ),
    ],
    routePrefix: '/v3/procuration',
    normalizationContext: ['groups' => ['procuration_proxy_list']],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 50,
    paginationMaximumItemsPerPage: 100,
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'procurations')"
)]
#[ExcludedAssociations]
#[ORM\Entity(repositoryClass: ProxyRepository::class)]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['created_at'])]
#[ORM\Table(name: 'procuration_proxies')]
class Proxy extends AbstractProcuration
{
    #[Assert\Length(min: 5,
        max: 9)]
    #[Assert\Regex(pattern: '/^[0-9]+$/i')]
    #[ORM\Column(length: 9, nullable: true)]
    public ?string $electorNumber = null;

    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $acceptVoteNearby = false;

    #[Assert\Expression(expression: 'value >= 1 and ((!this.isFDE() and value == 1) or (this.isFDE() and value <= 3))', message: 'procuration.proxy.slots.invalid')]
    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list'])]
    #[ORM\Column(type: 'smallint', options: ['default' => 1, 'unsigned' => true])]
    public int $slots = 1;

    #[Assert\Choice(callback: [ProxyStatusEnum::class, 'getAvailableStatuses'], groups: ['procuration_update_status'])]
    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list', 'procuration_update_status', 'procuration_proxy_slot_read', 'procuration_request_slot_read', 'procuration_request_read'])]
    #[ORM\Column(enumType: ProxyStatusEnum::class)]
    public ProxyStatusEnum $status = ProxyStatusEnum::PENDING;

    /**
     * @var Collection|ProxySlot[]
     */
    #[ORM\OneToMany(mappedBy: 'proxy', targetEntity: ProxySlot::class, cascade: ['all'], fetch: 'EXTRA_LAZY')]
    public Collection $proxySlots;

    /**
     * @var ProxyAction[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'proxy', targetEntity: ProxyAction::class, cascade: ['all'])]
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

        $this->proxySlots = new ArrayCollection();
        $this->actions = new ArrayCollection();
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
            if (!$proxySlot->requestSlot && !$proxySlot->manual) {
                return true;
            }
        }

        return false;
    }

    public function hasMatchedSlot(): bool
    {
        foreach ($this->proxySlots as $proxySlot) {
            if ($proxySlot->requestSlot) {
                return true;
            }
        }

        return false;
    }

    public function hasManualSlot(): bool
    {
        foreach ($this->proxySlots as $proxySlot) {
            if ($proxySlot->manual) {
                return true;
            }
        }

        return false;
    }

    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list'])]
    #[SerializedName('proxy_slots')]
    public function getOrderedSlots(): array
    {
        $slots = $this->proxySlots->toArray();

        uasort($slots, fn (ProxySlot $a, ProxySlot $b) => $a->round->date <=> $b->round->date);

        return array_values($slots);
    }

    public function markAsDuplicate(?string $detail): void
    {
        $this->status = ProxyStatusEnum::DUPLICATE;
        $this->statusDetail = $detail;
    }
}
