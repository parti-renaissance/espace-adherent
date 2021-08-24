<?php

namespace App\Entity\Jecoute;

use App\Entity\Device;
use App\Validator\DataSurveyConstraint;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @DataSurveyConstraint
 */
class JemarcheDataSurvey
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Device")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $device;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Email
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="boolean")
     */
    private $agreedToStayInContact = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $agreedToContactForJoin = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $agreedToTreatPersonalData = false;

    /**
     * @ORM\Column(length=5, nullable=true)
     *
     * @Assert\Length(min=5, max=5)
     */
    private $postalCode;

    /**
     * @ORM\Column(length=30, nullable=true)
     *
     * @Assert\Choice(callback={"App\Jecoute\ProfessionEnum", "all"})
     */
    private $profession;

    /**
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Choice(callback={"App\Jecoute\AgeRangeEnum", "all"})
     */
    private $ageRange;

    /**
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Choice(callback={"App\Jecoute\GenderEnum", "all"})
     */
    private $gender;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $genderOther;

    /**
     * @var float|null
     *
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $latitude;

    /**
     * @var float|null
     *
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $longitude;

    public function __construct(DataSurvey $dataSurvey = null, string $firstName = null, string $lastName = null)
    {
        $this->dataSurvey = $dataSurvey;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
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

    public function setDevice(?Device $device): void
    {
        $this->device = $device;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getAgreedToStayInContact(): bool
    {
        return $this->agreedToStayInContact;
    }

    public function setAgreedToStayInContact(bool $agreedToStayInContact): void
    {
        $this->agreedToStayInContact = $agreedToStayInContact;
    }

    public function getAgreedToContactForJoin(): bool
    {
        return $this->agreedToContactForJoin;
    }

    public function setAgreedToContactForJoin(bool $agreedToContactForJoin): void
    {
        $this->agreedToContactForJoin = $agreedToContactForJoin;
    }

    public function getAgreedToTreatPersonalData(): bool
    {
        return $this->agreedToTreatPersonalData;
    }

    public function setAgreedToTreatPersonalData(bool $agreedToTreatPersonalData): void
    {
        $this->agreedToTreatPersonalData = $agreedToTreatPersonalData;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): void
    {
        $this->profession = $profession;
    }

    public function getAgeRange(): ?string
    {
        return $this->ageRange;
    }

    public function setAgeRange(?string $ageRange): void
    {
        $this->ageRange = $ageRange;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getGenderOther(): ?string
    {
        return $this->genderOther;
    }

    public function setGenderOther(?string $genderOther): void
    {
        $this->genderOther = $genderOther;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }
}
