<?php

namespace App\Entity\ProcurationV2;

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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="procuration_v2_proxies")
 * @ORM\Entity(repositoryClass="App\Repository\Procuration\ProxyRepository")
 *
 * @AssociatedSlots
 * @ExcludedAssociations
 */
class Proxy extends AbstractProcuration
{
    public const DEFAULT_SLOTS = 1;

    /**
     * @ORM\Column(length=9)
     *
     * @Assert\Length(
     *     min=7,
     *     max=9
     * )
     * @Assert\Regex(pattern="/^[0-9]+$/i")
     */
    public string $electorNumber;

    /**
     * @ORM\Column(type="smallint", options={"default": 1, "unsigned": true})
     *
     * @Assert\Range(
     *     min=1,
     *     max=2
     * )
     */
    public int $slots = self::DEFAULT_SLOTS;

    /**
     * @ORM\Column(enumType=ProxyStatusEnum::class)
     */
    public ProxyStatusEnum $status = ProxyStatusEnum::PENDING;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProcurationV2\Request", mappedBy="proxy", cascade={"all"})
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

    public function addRequest(Request $request): void
    {
        if (!$this->requests->contains($request)) {
            $request->proxy = $this;
            $this->requests->add($request);
        }
    }

    public function removeRequest(Request $request): void
    {
        $request->proxy = null;
        $this->requests->removeElement($request);
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
