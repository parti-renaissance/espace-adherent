<?php

namespace App\Entity\AdherentMessage\Filter;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Committee;
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
 * @ApiResource(
 *     itemOperations={},
 *     collectionOperations={},
 *     subresourceOperations={},
 * )
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
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"audience_segment_read", "audience_segment_write", "adherent_message_update_filter"})
     */
    protected $isCertified;

    /**
     * @var Zone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Groups({"audience_segment_read", "audience_segment_write", "adherent_message_update_filter"})
     *
     * @Assert\Expression("this.getSegment() or this.getZone() or this.getCommittee()", message="Cette valeur ne doit pas être vide.")
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

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Groups({"adherent_message_update_filter"})
     */
    private ?Committee $committee = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"adherent_message_update_filter"})
     */
    private ?bool $isCommitteeMember = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"adherent_message_update_filter"})
     *
     * @Assert\Choice(choices=App\Entity\ElectedRepresentative\MandateTypeEnum::TYPE_CHOICES_CONTACTS, strict=true)
     */
    private ?string $mandateType = null;

    public function __construct()
    {
        $this->zones = new ArrayCollection();
    }

    /**
     * @Groups({"audience_segment_read"})
     */
    public function getIsCommitteeMember(): ?bool
    {
        return $this->isCommitteeMember;
    }

    /**
     * @Groups({"audience_segment_write"})
     */
    public function setIsCommitteeMember(?bool $value): void
    {
        $this->isCommitteeMember = $value;
    }

    public function getIsCertified(): ?bool
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

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function getMandateType(): ?string
    {
        return $this->mandateType;
    }

    public function setMandateType(?string $mandateType): void
    {
        $this->mandateType = $mandateType;
    }

    public function reset(): void
    {
        $this->generalFilterTraitReset();

        $this->isCertified = null;
        $this->audienceType = null;
        $this->committee = null;
        $this->isCommitteeMember = null;
        $this->mandateType = null;

        parent::reset();
    }
}
