<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiResource;
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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JemarcheDataSurveyRepository::class)]
#[ApiResource(attributes: ['normalization_context' => ['iri' => true, 'groups' => ['jemarche_data_survey_read']], 'denormalization_context' => ['groups' => ['jemarche_data_survey_write']]], itemOperations: ['post_reply' => ['path' => '/v3/jemarche_data_surveys/{uuid}/reply', 'method' => 'POST', 'requirements' => ['uuid' => '%pattern_uuid%'], 'controller' => 'App\Controller\Api\Jecoute\JemarcheDataSurveyReplyController', 'security' => "(is_granted('ROLE_ADHERENT') or is_granted('ROLE_OAUTH_DEVICE')) and (is_granted('ROLE_OAUTH_SCOPE_JECOUTE_SURVEYS') or is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))", 'defaults' => ['_api_receive' => false], 'normalization_context' => ['groups' => ['data_survey_read']]]], collectionOperations: ['post' => ['path' => '/v3/jemarche_data_surveys', 'security' => "(is_granted('ROLE_ADHERENT') or is_granted('ROLE_OAUTH_DEVICE')) and (is_granted('ROLE_OAUTH_SCOPE_JECOUTE_SURVEYS') or is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))"], 'get_jemarche_data_surveys_dashboard_kpi' => ['method' => 'GET', 'path' => '/v3/jemarche_data_surveys/kpi', 'controller' => 'App\Controller\Api\Jecoute\JemarcheDataSurveyKpiController', 'security' => "is_granted('IS_FEATURE_GRANTED', 'survey')"]])]
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

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Choice(callback: [ProfessionEnum::class, 'all'])]
    private $profession;

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Choice(callback: [AgeRangeEnum::class, 'all'])]
    private $ageRange;

    #[Groups(['jemarche_data_survey_write'])]
    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Choice(callback: [GenderEnum::class, 'all'])]
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

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(inversedBy: 'jemarcheDataSurvey', targetEntity: DataSurvey::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
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
