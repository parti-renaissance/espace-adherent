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
class AudienceFilter extends AbstractAdherentMessageFilter implements ZoneableEntity, CampaignAdherentMessageFilterInterface
{
    use GeneralFilterTrait {
        GeneralFilterTrait::reset as generalFilterTraitReset;
    }

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
     * @Groups({"audience_segment_read", "audience_segment_write", "adherent_message_update_filter"})
     */
    protected $isCertified;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_segment_read", "audience_segment_write", "adherent_message_update_filter"})
     */
    protected ?bool $isRenaissanceMembership = null;

    /**
     * @var Zone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Groups({"audience_segment_read", "audience_segment_write", "adherent_message_update_filter"})
     *
     * @Assert\Expression("this.getSegment() or this.getZone()", message="Cette valeur ne doit pas être vide.")
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
     * @ORM\Column
     *
     * @Assert\Expression("this.getSegment() or this.getScope()", message="Cette valeur ne doit pas être vide.")
     * @ValidScope
     *
     * @Groups({"audience_segment_read", "audience_segment_write", "adherent_message_update_filter"})
     */
    private $scope;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"adherent_message_update_filter"})
     */
    private $audienceType;

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
     * @Groups({"audience_segment_write", "adherent_message_update_filter"})
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

    public function isRenaissanceMembership(): ?bool
    {
        return $this->isRenaissanceMembership;
    }

    public function setIsRenaissanceMembership(?bool $isRenaissanceMembership): void
    {
        $this->isRenaissanceMembership = $isRenaissanceMembership;
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
     * @Groups({"audience_segment_write", "adherent_message_update_filter"})
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
     * @Groups({"audience_segment_write", "adherent_message_update_filter"})
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

    public function getAudienceType(): ?string
    {
        return $this->audienceType;
    }

    public function setAudienceType(?string $audienceType): void
    {
        $this->audienceType = $audienceType;
    }

    public function reset(): void
    {
        $this->generalFilterTraitReset();

        $this->includeAdherentsNoCommittee = true;
        $this->includeAdherentsInCommittee = true;
        $this->isCertified = null;
        $this->isRenaissanceMembership = null;
        $this->audienceType = null;

        parent::reset();
    }
}
