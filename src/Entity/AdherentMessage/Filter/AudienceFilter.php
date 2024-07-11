<?php

namespace App\Entity\AdherentMessage\Filter;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Adherent\MandateTypeEnum;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Entity\ZoneableEntity;
use App\Validator\ManagedZone;
use App\Validator\ValidScope;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={},
 *     collectionOperations={},
 *     subresourceOperations={},
 * )
 * @ManagedZone(zoneGetMethodName="getZone")
 */
#[ORM\Entity]
class AudienceFilter extends AbstractAdherentMessageFilter implements ZoneableEntity, CampaignAdherentMessageFilterInterface
{
    use GeneralFilterTrait {
        GeneralFilterTrait::reset as generalFilterTraitReset;
    }

    /**
     * @var bool|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $isCertified;

    /**
     * @var Zone|null
     */
    #[Assert\Expression('this.getSegment() or this.getZone() or this.getCommittee()', message: 'Cette valeur ne doit pas être vide.')]
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    private $zone;

    /**
     * @var string|null
     *
     * @ValidScope
     */
    #[Assert\Expression('this.getSegment() or this.getScope()', message: 'Cette valeur ne doit pas être vide.')]
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column]
    private $scope;

    /**
     * @var string|null
     */
    #[Groups(['adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    private $audienceType;

    #[Groups(['adherent_message_update_filter'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Committee::class)]
    private ?Committee $committee = null;

    #[Groups(['adherent_message_update_filter'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isCommitteeMember = null;

    #[Assert\Choice(choices: MandateTypeEnum::ALL)]
    #[Groups(['adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    private ?string $mandateType = null;

    #[Assert\Choice(choices: MandateTypeEnum::ALL)]
    #[Groups(['adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    private ?string $declaredMandate = null;

    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected ?bool $isCampusRegistered = null;

    #[Groups(['adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    private ?string $donatorStatus = null;

    #[Groups(['adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    public ?string $adherentTags = null;

    #[Groups(['adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    public ?string $electTags = null;

    #[Groups(['adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    public ?string $staticTags = null;

    #[Groups(['audience_segment_read'])]
    public function getIsCommitteeMember(): ?bool
    {
        return $this->isCommitteeMember;
    }

    #[Groups(['audience_segment_write'])]
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

    #[Groups(['audience_segment_write', 'adherent_message_update_filter'])]
    public function setAge(array $minMax): void
    {
        if (!empty($minMax['min'])) {
            $this->setAgeMin($minMax['min']);
        }

        if (!empty($minMax['max'])) {
            $this->setAgeMax($minMax['max']);
        }
    }

    #[Groups(['audience_segment_write', 'adherent_message_update_filter'])]
    public function setRegistered(array $startEnd): void
    {
        if (!empty($startEnd['start'])) {
            $this->setRegisteredSince(new \DateTime($startEnd['start']));
        }

        if (!empty($startEnd['end'])) {
            $this->setRegisteredUntil(new \DateTime($startEnd['end']));
        }
    }

    #[Groups(['audience_segment_write', 'adherent_message_update_filter'])]
    public function setLastMembership(array $startEnd): void
    {
        if (!empty($startEnd['start'])) {
            $this->setLastMembershipSince(new \DateTime($startEnd['start']));
        }

        if (!empty($startEnd['end'])) {
            $this->setLastMembershipBefore(new \DateTime($startEnd['end']));
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

    public function getDeclaredMandate(): ?string
    {
        return $this->declaredMandate;
    }

    public function setDeclaredMandate(?string $declaredMandate): void
    {
        $this->declaredMandate = $declaredMandate;
    }

    public function getIsCampusRegistered(): ?bool
    {
        return $this->isCampusRegistered;
    }

    public function setIsCampusRegistered(?bool $isCampusRegistered): void
    {
        $this->isCampusRegistered = $isCampusRegistered;
    }

    public function getDonatorStatus(): ?string
    {
        return $this->donatorStatus;
    }

    public function setDonatorStatus(?string $donatorStatus): void
    {
        $this->donatorStatus = $donatorStatus;
    }

    public function reset(): void
    {
        $this->generalFilterTraitReset();

        $this->isCertified = null;
        $this->audienceType = null;
        $this->committee = null;
        $this->isCommitteeMember = null;
        $this->mandateType = null;
        $this->declaredMandate = null;
        $this->isCampusRegistered = null;
        $this->donatorStatus = null;
        $this->adherentTags = null;
        $this->electTags = null;
        $this->staticTags = null;

        parent::reset();
    }
}
