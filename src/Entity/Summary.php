<?php

namespace AppBundle\Entity;

use AppBundle\Entity\MemberSummary\JobExperience;
use AppBundle\Entity\MemberSummary\Language;
use AppBundle\Entity\MemberSummary\MissionType;
use AppBundle\Entity\MemberSummary\Skill;
use AppBundle\Entity\MemberSummary\Training;
use AppBundle\Summary\Contribution;
use AppBundle\Summary\JobDuration;
use AppBundle\Summary\JobLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SummaryRepository")
 * @ORM\Table(name="summaries")
 */
class Summary
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Adherent
     *
     * @ORM\OneToOne(targetEntity="Adherent")
     */
    private $member;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=200, groups={"synthese"})
     */
    private $currentProfession;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(groups={"synthese"})
     * @Assert\Choice(strict=true, callback={"\AppBundle\Summary\Contribution", "all"}, groups={"synthese"})
     */
    private $contributionWish = '';

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\Count(min=1, groups={"synthese"})
     */
    private $availabilities = [];

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\Count(min=1, groups={"synthese"})
     */
    private $jobLocations = [];

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(groups={"synthese"})
     * @Assert\Length(min=10, max=300, groups={"synthese"})
     */
    private $professionalSynopsis = '';

    /**
     * @var MissionType[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\MemberSummary\MissionType")
     * @ORM\JoinTable(
     *     name="summary_mission_type_wishes",
     *     joinColumns={
     *         @ORM\JoinColumn(name="summary_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="mission_type_id", referencedColumnName="id")
     *     }
     * )
     *
     * @Assert\Count(min=1, groups={"missions"})
     */
    private $missionTypeWishes;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(groups={"motivation"})
     * @Assert\Length(min=10, max=300, groups={"motivation"})
     */
    private $motivation = '';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $showingRecentActivities = false;

    /**
     * @var JobExperience[]|Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MemberSummary\JobExperience", mappedBy="summary", indexBy="id", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"displayOrder"="ASC"})
     */
    private $experiences;

    /**
     * @var Skill[]|Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MemberSummary\Skill", mappedBy="summary", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid
     * @Assert\Count(min=1, groups={"competences"})
     */
    private $skills;

    /**
     * @var Language[]|Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MemberSummary\Language", mappedBy="summary", indexBy="id", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid
     * @Assert\Count(min=1)
     */
    private $languages;

    /**
     * @var Training[]|Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MemberSummary\Training", mappedBy="summary", indexBy="id", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"displayOrder"="ASC"})
     *
     * @Assert\Valid
     */
    private $trainings;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(groups={"contact"})
     * @Assert\Email(groups={"contact"})
     * @Assert\Length(max=255, groups={"contact"})
     */
    private $contactEmail = '';

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups={"contact"})
     * @Assert\Length(max=255, groups={"contact"})
     */
    private $linkedInUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups={"contact"})
     * @Assert\Length(max=255, groups={"contact"})
     */
    private $websiteUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups={"contact"})
     * @Assert\Length(max=255, groups={"contact"})
     */
    private $facebookUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"contact"})
     */
    private $twitterNickname;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url(groups={"contact"})
     * @Assert\Length(max=255, groups={"contact"})
     */
    private $viadeoUrl;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $public = false;

    public function __construct(Adherent $adherent, string $slug)
    {
        $this->member = $adherent;
        $this->slug = $slug;
        $this->missionTypeWishes = new ArrayCollection();
        $this->experiences = new ArrayCollection();
        $this->skills = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->trainings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): Adherent
    {
        return $this->member;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCurrentProfession(): ?string
    {
        return $this->currentProfession;
    }

    public function setCurrentProfession(?string $currentProfession): void
    {
        $this->currentProfession = $currentProfession;
    }

    /**
     * @Assert\Choice(
     *     callback = {"AppBundle\Membership\ActivityPositions", "all"},
     *     message="adherent.activity_position.invalid_choice",
     *     strict=true,
     *     groups={"synthese"}
     * )
     */
    public function getCurrentPosition(): string
    {
        return $this->member->getPosition();
    }

    public function setCurrentPosition(string $position): void
    {
        $this->member->setPosition($position);
    }

    public function getContributionWish(): string
    {
        return $this->contributionWish;
    }

    public function setContributionWish(string $contributionWish): void
    {
        $this->contributionWish = $contributionWish;
    }

    public function getContributionWishLabel(): string
    {
        return Contribution::MISSION_LABELS[$this->contributionWish] ?? '';
    }

    public function getAvailabilities(): array
    {
        return $this->availabilities;
    }

    public function setAvailabilities(array $availabilities): void
    {
        $this->availabilities = $availabilities;
    }

    public function getJobLocations(): array
    {
        return $this->jobLocations;
    }

    public function setJobLocations(array $jobLocations): void
    {
        $this->jobLocations = $jobLocations;
    }

    public function getProfessionalSynopsis(): string
    {
        return $this->professionalSynopsis;
    }

    public function setProfessionalSynopsis(string $professionalSynopsis): void
    {
        $this->professionalSynopsis = $professionalSynopsis;
    }

    /**
     * @return MissionType[]|Collection
     */
    public function getMissionTypeWishes(): iterable
    {
        return $this->missionTypeWishes;
    }

    /**
     * @param MissionType[]|Collection $missionTypeWishes
     */
    public function setMissionTypeWishes(iterable $missionTypeWishes): void
    {
        $this->missionTypeWishes = $missionTypeWishes;
    }

    public function getMotivation(): string
    {
        return $this->motivation;
    }

    public function setMotivation(string $motivation): void
    {
        $this->motivation = $motivation;
    }

    public function isShowingRecentActivities(): bool
    {
        return $this->showingRecentActivities;
    }

    public function setShowingRecentActivities(bool $showingRecentActivities): void
    {
        $this->showingRecentActivities = $showingRecentActivities;
    }

    /**
     * @return JobExperience[]|Collection
     */
    public function getExperiences(): iterable
    {
        return $this->experiences;
    }

    public function addExperience(JobExperience $experience): void
    {
        if (!$this->experiences->contains($experience)) {
            $experience->setSummary($this);
            $this->experiences->add($experience);
        }
    }

    public function removeExperience(JobExperience $experience): void
    {
        $this->experiences->removeElement($experience);
    }

    /**
     * @return Skill[]|Collection
     */
    public function getSkills(): iterable
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): void
    {
        if (!$this->skills->contains($skill)) {
            $skill->setSummary($this);
            $this->skills->add($skill);
        }
    }

    public function removeSkill(Skill $skill): void
    {
        $this->skills->removeElement($skill);
    }

    /**
     * @return Language[]|Collection
     */
    public function getLanguages(): iterable
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): void
    {
        if (!$this->languages->contains($language)) {
            $language->setSummary($this);
            $this->languages->add($language);
        }
    }

    public function removeLanguage(Language $language): void
    {
        $this->languages->removeElement($language);
    }

    public function getLanguagesByLevel(): iterable
    {
        return Language::sortByLevel($this->languages);
    }

    public function getMemberInterests(): array
    {
        return $this->member->getInterests();
    }

    public function setMemberInterests(array $interests): void
    {
        $this->member->setInterests($interests);
    }

    /**
     * @return Training[]|Collection
     */
    public function getTrainings(): iterable
    {
        return $this->trainings;
    }

    public function addTraining(Training $training): void
    {
        if (!$this->trainings->contains($training)) {
            $training->setSummary($this);
            $this->trainings->add($training);
        }
    }

    public function removeTraining(Training $training): void
    {
        $this->trainings->removeElement($training);
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function getLinkedInUrl(): ?string
    {
        return $this->linkedInUrl;
    }

    public function setLinkedInUrl(?string $linkedInUrl): void
    {
        $this->linkedInUrl = $linkedInUrl;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): void
    {
        $this->websiteUrl = $websiteUrl;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): void
    {
        $this->facebookUrl = $facebookUrl;
    }

    public function getTwitterNickname(): ?string
    {
        return $this->twitterNickname;
    }

    public function setTwitterNickname(?string $twitterNickname): void
    {
        $this->twitterNickname = $twitterNickname;
    }

    public function getViadeoUrl(): ?string
    {
        return $this->viadeoUrl;
    }

    public function setViadeoUrl(?string $viadeoUrl): void
    {
        $this->viadeoUrl = $viadeoUrl;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    /**
     * @Assert\IsTrue
     */
    public function hasValidAvailabilities(): bool
    {
        foreach ($this->availabilities as $availability) {
            if (!JobDuration::exists($availability)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @Assert\IsTrue
     */
    public function hasValidJobLocations(): bool
    {
        foreach ($this->jobLocations as $location) {
            if (!JobLocation::exists($location)) {
                return false;
            }
        }

        return true;
    }

    public static function createFromMember(Adherent $adherent, string $slug): self
    {
        $self = new self($adherent, $slug);

        return $self;
    }
}
