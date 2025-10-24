<?php

namespace App\Entity\AdherentMessage\Filter;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Entity\ZoneableEntityInterface;
use App\Validator\ManagedZone;
use App\Validator\ValidScope;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    uriTemplate: '/v3/adherent_messages/{uuid}/filter',
    operations: [new Get(security: "is_granted('CAN_EDIT_PUBLICATION', object.getMessage())")],
    uriVariables: [
        'uuid' => new Link(fromProperty: 'filter', fromClass: AdherentMessage::class),
    ],
    normalizationContext: ['groups' => ['adherent_message_read_filter']],
)]
#[ManagedZone]
#[ORM\Entity]
class AudienceFilter extends AbstractAdherentMessageFilter implements ZoneableEntityInterface, CampaignAdherentMessageFilterInterface
{
    use GeneralFilterTrait {
        GeneralFilterTrait::reset as generalFilterTraitReset;
    }

    /**
     * @var bool|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $isCertified;

    /**
     * @var Zone|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    private $zone;

    /**
     * @var string|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column]
    #[ValidScope]
    private $scope;

    /**
     * @var string|null
     */
    #[Groups(['adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    private $audienceType;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Committee::class)]
    private ?Committee $committee = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isCommitteeMember = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    private ?string $mandateType = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    private ?string $declaredMandate = null;

    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    protected ?bool $isCampusRegistered = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    private ?string $donatorStatus = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    public ?string $adherentTags = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
    #[ORM\Column(nullable: true)]
    public ?string $electTags = null;

    #[Groups(['adherent_message_update_filter', 'adherent_message_read_filter'])]
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
    public function setAge(?array $minMax): void
    {
        $this->setAgeMin(empty($minMax['min']) ? null : $minMax['min']);
        $this->setAgeMax(empty($minMax['max']) ? null : $minMax['max']);
    }

    #[Groups(['audience_segment_write', 'adherent_message_update_filter'])]
    public function setRegistered(?array $startEnd): void
    {
        $this->setRegisteredSince(empty($startEnd['start']) ? null : new \DateTime($startEnd['start']));
        $this->setRegisteredUntil(empty($startEnd['end']) ? null : new \DateTime($startEnd['end']));
    }

    #[Groups(['audience_segment_write', 'adherent_message_update_filter'])]
    public function setFirstMembership(?array $startEnd): void
    {
        $this->firstMembershipSince = empty($startEnd['start']) ? null : new \DateTime($startEnd['start']);
        $this->firstMembershipBefore = empty($startEnd['end']) ? null : new \DateTime($startEnd['end']);
    }

    #[Groups(['audience_segment_write', 'adherent_message_update_filter'])]
    public function setLastMembership(?array $startEnd): void
    {
        $this->setLastMembershipSince(empty($startEnd['start']) ? null : new \DateTime($startEnd['start']));
        $this->setLastMembershipBefore(empty($startEnd['end']) ? null : new \DateTime($startEnd['end']));
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

    public function setCommittee(?Committee $committee): void
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

    public function includeFilter(string $value): bool
    {
        return !str_starts_with($value, '!');
    }
}
