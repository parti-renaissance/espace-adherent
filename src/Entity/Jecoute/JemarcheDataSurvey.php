<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\Jecoute\JemarcheDataSurveyKpiController;
use App\Controller\Api\Jecoute\JemarcheDataSurveyReplyController;
use App\Entity\Device;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Jecoute\AgeRangeEnum;
use App\Jecoute\GenderEnum;
use App\Jecoute\ProfessionEnum;
use App\Repository\Jecoute\JemarcheDataSurveyRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/v3/jemarche_data_surveys/{uuid}/reply',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: JemarcheDataSurveyReplyController::class,
            normalizationContext: ['groups' => ['data_survey_read']],
            security: "(is_granted('ROLE_USER') or is_granted('ROLE_OAUTH_DEVICE')) and (is_granted('ROLE_OAUTH_SCOPE_JECOUTE_SURVEYS') or is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))",
            deserialize: false
        ),
        new Post(
            uriTemplate: '/v3/jemarche_data_surveys',
            security: "(is_granted('ROLE_USER') or is_granted('ROLE_OAUTH_DEVICE')) and (is_granted('ROLE_OAUTH_SCOPE_JECOUTE_SURVEYS') or is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))"
        ),
        new GetCollection(
            uriTemplate: '/v3/jemarche_data_surveys/kpi',
            controller: JemarcheDataSurveyKpiController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'survey')"
        ),
    ],
    normalizationContext: ['iri' => true, 'groups' => ['jemarche_data_survey_read']],
    denormalizationContext: ['groups' => ['jemarche_data_survey_write']]
)]
#[ORM\Entity(repositoryClass: JemarcheDataSurveyRepository::class)]
class JemarcheDataSurvey implements DataSurveyAwareInterface
{
    use EntityIdentityTrait;
    use DataSurveyAwareTrait;
    use EntityTimestampableTrait;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Device::class)]
    private $device;

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $firstName;

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $lastName;

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $emailAddress;

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(type: 'boolean')]
    private $agreedToStayInContact = false;

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(type: 'boolean')]
    private $agreedToContactForJoin = false;

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(type: 'boolean')]
    private $agreedToTreatPersonalData = false;

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $postalCode;

    #[Assert\Choice(callback: [ProfessionEnum::class, 'all'])]
    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(length: 30, nullable: true)]
    private $profession;

    #[Assert\Choice(callback: [AgeRangeEnum::class, 'all'])]
    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(length: 15, nullable: true)]
    private $ageRange;

    #[Assert\Choice(callback: [GenderEnum::class, 'all'])]
    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(length: 15, nullable: true)]
    private $gender;

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $genderOther;

    /**
     * @var float|null
     */
    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(type: 'geo_point', nullable: true)]
    private $latitude;

    /**
     * @var float|null
     */
    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(type: 'geo_point', nullable: true)]
    private $longitude;

    #[Assert\Valid]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(inversedBy: 'jemarcheDataSurvey', targetEntity: DataSurvey::class, cascade: ['persist'], orphanRemoval: true)]
    private ?DataSurvey $dataSurvey = null;

    public function __construct(?UuidInterface $uuid = null)
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
