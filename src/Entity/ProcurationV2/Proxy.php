<?php

namespace App\Entity\ProcurationV2;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Validator\Procuration\AssociatedSlots;
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
 */
class Proxy extends AbstractProcuration
{
    /**
     * @ORM\Column(length=9)
     *
     * @Assert\NotBlank
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
    public int $slots;

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
}
