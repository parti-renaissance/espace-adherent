<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Device;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Validator\JemarcheDataSurveyConstraint;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\JemarcheDataSurveyRepository")
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"jemarche_data_survey_read"},
 *         },
 *         "denormalization_context": {
 *             "groups": {"jemarche_data_survey_write"},
 *         },
 *     },
 *     itemOperations={
 *         "post_reply": {
 *             "path": "/v3/jemarche_data_surveys/{uuid}/reply",
 *             "method": "POST",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Jecoute\JemarcheDataSurveyReplyController",
 *             "access_control": "(is_granted('ROLE_ADHERENT') or is_granted('ROLE_OAUTH_DEVICE')) and (is_granted('ROLE_OAUTH_SCOPE_JECOUTE_SURVEYS') or is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))",
 *             "defaults": {"_api_receive": false},
 *             "normalization_context": {"groups": {"data_survey_read"}},
 *         },
 *     },
 *     collectionOperations={
 *         "post": {
 *             "path": "/v3/jemarche_data_surveys",
 *             "access_control": "(is_granted('ROLE_ADHERENT') or is_granted('ROLE_OAUTH_DEVICE')) and (is_granted('ROLE_OAUTH_SCOPE_JECOUTE_SURVEYS') or is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))"
 *         },
 *     },
 * )
 * @JemarcheDataSurveyConstraint
 */
class JemarcheDataSurvey implements DataSurveyAwareInterface
{
    use EntityIdentityTrait;
    use DataSurveyAwareTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Device")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $device;

    /**
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\Length(max=50, maxMessage="common.first_name.max_length")
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $firstName;

    /**
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\Length(max=50, maxMessage="common.last_name.max_length")
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $lastName;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Email
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $agreedToStayInContact = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $agreedToContactForJoin = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $agreedToTreatPersonalData = false;

    /**
     * @ORM\Column(length=5, nullable=true)
     *
     * @Assert\Length(min=5, max=5)
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $postalCode;

    /**
     * @ORM\Column(length=30, nullable=true)
     *
     * @Assert\Choice(callback={"App\Jecoute\ProfessionEnum", "all"})
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $profession;

    /**
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Choice(callback={"App\Jecoute\AgeRangeEnum", "all"})
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $ageRange;

    /**
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Choice(callback={"App\Jecoute\GenderEnum", "all"})
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $gender;

    /**
     * @ORM\Column(length=50, nullable=true)
     *
     * @Assert\Length(max=50)
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $genderOther;

    /**
     * @var float|null
     *
     * @ORM\Column(type="geo_point", nullable=true)
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $latitude;

    /**
     * @var float|null
     *
     * @ORM\Column(type="geo_point", nullable=true)
     *
     * @Groups({"jemarche_data_survey_write"})
     */
    private $longitude;

    /**
     * @var DataSurvey|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Jecoute\DataSurvey", cascade={"persist"}, orphanRemoval=true, inversedBy="jemarcheDataSurvey")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    private $dataSurvey;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
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
