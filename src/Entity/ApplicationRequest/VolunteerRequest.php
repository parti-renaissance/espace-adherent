<?php

namespace App\Entity\ApplicationRequest;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\ApplicationRequest\ApplicationRequestTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="application_request_volunteer")
 * @ORM\Entity(repositoryClass="App\Repository\ApplicationRequest\VolunteerRequestRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class VolunteerRequest extends ApplicationRequest
{
    /**
     * @var TechnicalSkill[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ApplicationRequest\TechnicalSkill")
     *
     * @Assert\Count(min=1, minMessage="application_request.technical_skills.min")
     */
    private $technicalSkills;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $customTechnicalSkills;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean")
     */
    private $isPreviousCampaignMember;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $previousCampaignDetails;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean")
     */
    private $shareAssociativeCommitment;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $associativeCommitmentDetails;

    public function __construct(UuidInterface $uuid = null)
    {
        parent::__construct($uuid);

        $this->technicalSkills = new ArrayCollection();
    }

    public function getTechnicalSkills(): Collection
    {
        return $this->technicalSkills;
    }

    public function addTechnicalSkill(TechnicalSkill $technicalSkill): void
    {
        if (!$this->technicalSkills->contains($technicalSkill)) {
            $this->technicalSkills->add($technicalSkill);
        }
    }

    public function removeTechnicalSkill(TechnicalSkill $technicalSkill): void
    {
        $this->technicalSkills->removeElement($technicalSkill);
    }

    public function getCustomTechnicalSkills(): ?string
    {
        return $this->customTechnicalSkills;
    }

    public function setCustomTechnicalSkills(?string $customTechnicalSkills): void
    {
        $this->customTechnicalSkills = $customTechnicalSkills;
    }

    public function isPreviousCampaignMember(): ?bool
    {
        return $this->isPreviousCampaignMember;
    }

    public function setIsPreviousCampaignMember(bool $isPreviousCampaignMember): void
    {
        $this->isPreviousCampaignMember = $isPreviousCampaignMember;
    }

    public function getPreviousCampaignDetails(): ?string
    {
        return $this->previousCampaignDetails;
    }

    public function setPreviousCampaignDetails(?string $previousCampaignDetails): void
    {
        $this->previousCampaignDetails = $previousCampaignDetails;
    }

    public function getShareAssociativeCommitment(): ?bool
    {
        return $this->shareAssociativeCommitment;
    }

    public function setShareAssociativeCommitment(bool $shareAssociativeCommitment): void
    {
        $this->shareAssociativeCommitment = $shareAssociativeCommitment;
    }

    public function getAssociativeCommitmentDetails(): ?string
    {
        return $this->associativeCommitmentDetails;
    }

    public function setAssociativeCommitmentDetails(?string $associativeCommitmentDetails): void
    {
        $this->associativeCommitmentDetails = $associativeCommitmentDetails;
    }

    public function getTechnicalSkillsAsString(): string
    {
        $skills = array_map(function (TechnicalSkill $theme) {
            return $theme->getName();
        }, $this->technicalSkills->toArray());

        if (!empty($this->customTechnicalSkills)) {
            $skills[] = $this->customTechnicalSkills;
        }

        return implode(', ', $skills);
    }

    public function isPreviousCampaignMemberAsString(): string
    {
        return $this->isPreviousCampaignMember ? 'Oui' : 'Non';
    }

    public function shareAssociativeCommitmentAsString(): string
    {
        return $this->shareAssociativeCommitment ? 'Oui' : 'Non';
    }

    public function getType(): string
    {
        return ApplicationRequestTypeEnum::VOLUNTEER;
    }
}
