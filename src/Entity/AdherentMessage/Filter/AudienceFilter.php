<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Entity\ZoneableEntity;
use App\Validator\ManagedZone;
use App\Validator\ValidScope;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @ManagedZone(zoneGetMethodName="getZone")
 */
class AudienceFilter extends AbstractAdherentFilter implements ZoneableEntity
{
    use GeneralFilterTrait;
    use EntityZoneTrait;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     *
     * @Groups({"audience_segment_write"})
     */
    private $includeAdherentsNoCommittee = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     *
     * @Groups({"audience_segment_write"})
     */
    private $includeAdherentsInCommittee = true;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_segment_read", "audience_segment_write"})
     */
    protected $isCertified;

    /**
     * @var Zone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Groups({"audience_segment_read", "audience_segment_write"})
     *
     * @Assert\NotBlank
     */
    private $zone;

    /**
     * Managed zone collection, useful for validate selected zone ($zone property)
     *
     * @var Collection|Zone[]
     */
    protected $zones;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20)
     *
     * @Assert\NotNull
     * @Assert\Choice(
     *     choices=App\Scope\ScopeEnum::FOR_AUDIENCE_SEGMENT,
     *     message="audience_segment.scope.invalid_choice",
     *     strict=true
     * )
     * @ValidScope
     *
     * @Groups({"audience_segment_read", "audience_segment_write"})
     */
    private $scope;

    public function __construct()
    {
        $this->zones = new ArrayCollection();
    }

    public function includeAdherentsNoCommittee(): ?bool
    {
        return $this->includeAdherentsNoCommittee;
    }

    public function setIncludeAdherentsNoCommittee(?bool $value): void
    {
        $this->includeAdherentsNoCommittee = $value;
    }

    public function includeAdherentsInCommittee(): ?bool
    {
        return $this->includeAdherentsInCommittee;
    }

    public function setIncludeAdherentsInCommittee(?bool $value): void
    {
        $this->includeAdherentsInCommittee = $value;
    }

    /**
     * @Groups({"audience_segment_read"})
     */
    public function getIsCommitteeMember(): ?bool
    {
        return $this->includeAdherentsInCommittee ? true : ($this->includeAdherentsNoCommittee ? false : null);
    }

    /**
     * @Groups({"audience_segment_write"})
     */
    public function setIsCommitteeMember(?bool $value): void
    {
        if (null !== $value) {
            $this->includeAdherentsInCommittee = $value;
            $this->includeAdherentsNoCommittee = !$value;
        }
    }

    public function isCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function setIsCertified(?bool $isCertified): void
    {
        $this->isCertified = $isCertified;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * @Groups({"audience_segment_write"})
     */
    public function setAge(array $minMax): void
    {
        if (!empty($minMax['min'])) {
            $this->setAgeMin($minMax['min']);
        }

        if (!empty($minMax['max'])) {
            $this->setAgeMax($minMax['max']);
        }
    }

    /**
     * @Groups({"audience_segment_write"})
     */
    public function setRegistered(array $startEnd): void
    {
        if (!empty($startEnd['start'])) {
            $this->setRegisteredSince(new \DateTime($startEnd['start']));
        }

        if (!empty($startEnd['end'])) {
            $this->setRegisteredUntil(new \DateTime($startEnd['end']));
        }
    }
}
