<?php

namespace App\Entity\Jecoute;

use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;
use App\Validator\DataSurveyConstraint;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @DataSurveyConstraint
 */
class PhoningDataSurvey
{
    /**
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var DataSurvey
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Jecoute\DataSurvey")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    private $dataSurvey;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Phoning\Campaign")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $campaign;

    /**
     * @var string
     *
     * @ORM\Column(length=10, nullable=true)
     *
     * @Assert\NotNull
     * @Assert\Choice(
     *     callback={"App\Phoning\DataSurveyTypeEnum", "toArray"},
     *     message="phoning.data_survey.type.invalid_choice",
     *     strict=true
     * )
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(length=25)
     *
     * @Assert\NotNull
     * @Assert\Choice(
     *     callback={"App\Phoning\DataSurveyStatusEnum", "toArray"},
     *     message="phoning.data_survey.status.invalid_choice",
     *     strict=true
     * )
     */
    private $status;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $postalCodeChecked;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $callMore;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $needRenewal;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $becomeCaller;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $beginAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     * @Assert\Expression(
     *     "value === null or value > this.getBeginAt()",
     *     message="phoning.data_survey.finish_at.invalid"
     * )
     */
    private $finishAt;

    public function __construct(DataSurvey $dataSurvey = null, Adherent $adherent = null)
    {
        $this->dataSurvey = $dataSurvey;
        $this->adherent = $adherent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDataSurvey(): ?DataSurvey
    {
        return $this->dataSurvey;
    }

    public function setDataSurvey(DataSurvey $dataSurvey): void
    {
        $this->dataSurvey = $dataSurvey;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isPostalCodeChecked(): ?bool
    {
        return $this->postalCodeChecked;
    }

    public function setPostalCodeChecked(bool $postalCodeChecked): void
    {
        $this->postalCodeChecked = $postalCodeChecked;
    }

    public function isCallMore(): ?bool
    {
        return $this->callMore;
    }

    public function setCallMore(bool $callMore): void
    {
        $this->callMore = $callMore;
    }

    public function isNeedRenewal(): ?bool
    {
        return $this->needRenewal;
    }

    public function setNeedRenewal(bool $needRenewal): void
    {
        $this->needRenewal = $needRenewal;
    }

    public function isBecomeCaller(): ?bool
    {
        return $this->becomeCaller;
    }

    public function setBecomeCaller(bool $becomeCaller): void
    {
        $this->becomeCaller = $becomeCaller;
    }

    public function getBeginAt(): \DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTimeInterface $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTimeInterface $finishAt): void
    {
        $this->finishAt = $finishAt;
    }
}
