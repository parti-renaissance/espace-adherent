<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\JeMengageSurveyScopeFilter;
use App\Entity\Adherent;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\IndexableEntityInterface;
use App\Jecoute\SurveyTypeEnum;
use App\Validator\Jecoute\SurveyScopeTarget;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\SurveyRepository")
 * @ORM\Table(name="jecoute_survey")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     SurveyTypeEnum::LOCAL: "LocalSurvey",
 *     SurveyTypeEnum::NATIONAL: "NationalSurvey"
 * })
 *
 * @ORM\EntityListeners({"App\EntityListener\AlgoliaIndexListener"})
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {"groups": {"survey_list"}},
 *         "filters": {JeMengageSurveyScopeFilter::class},
 *         "order": {"createdAt": "DESC"},
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/surveys/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'survey') and is_granted('CAN_MANAGE_SURVEY', object)",
 *             "normalization_context": {
 *                 "groups": {"survey_read_dc"}
 *             }
 *         },
 *         "put": {
 *             "path": "/v3/surveys/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'survey') and is_granted('CAN_MANAGE_SURVEY', object)",
 *             "denormalization_context": {"groups": {"survey_update_dc"}},
 *             "normalization_context": {"groups": {"survey_read_dc"}},
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/surveys",
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'survey') or is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')",
 *             "normalization_context": {
 *                 "groups": {"survey_list_dc"}
 *             }
 *         },
 *         "post": {
 *             "path": "/v3/surveys",
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'survey')",
 *             "denormalization_context": {
 *                 "groups": {"survey_write_dc"},
 *             },
 *             "normalization_context": {
 *                 "groups": {"survey_read_dc"}
 *             }
 *         }
 *     },
 *     subresourceOperations={
 *         "api_phoning_campaigns_survey_get_subresource": {
 *             "access_control": "is_granted('ROLE_PHONING_CAMPAIGN_MEMBER')",
 *         },
 *         "api_pap_campaigns_survey_get_subresource": {
 *             "access_control": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')",
 *         },
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "partial",
 * })
 *
 * @SurveyScopeTarget
 */
abstract class Survey implements IndexableEntityInterface, EntityAdministratorBlameableInterface, EntityAdherentBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     *
     * @SymfonySerializer\Groups({
     *     "data_survey_write",
     *     "data_survey_read",
     *     "jemarche_data_survey_read",
     *     "survey_list",
     *     "survey_list_dc",
     *     "survey_read_dc",
     *     "phoning_campaign_read",
     *     "phoning_campaign_history_read_list",
     *     "pap_campaign_read_after_write",
     *     "pap_campaign_history_read_list",
     *     "phoning_campaign_replies_list",
     *     "pap_campaign_replies_list",
     * })
     *
     * @ApiProperty(identifier=true)
     */
    protected $uuid;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=70)
     *
     * @SymfonySerializer\Groups(
     *     "survey_list",
     *     "survey_list_dc",
     *     "survey_write_dc",
     *     "survey_update_dc",
     *     "survey_read_dc",
     *     "phoning_campaign_read",
     *     "phoning_campaign_history_read_list",
     *     "phoning_campaign_replies_list",
     *     "pap_campaign_replies_list",
     *     "pap_campaign_history_read_list",
     * )
     */
    private $name;

    /**
     * @var SurveyQuestion[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Jecoute\SurveyQuestion", mappedBy="survey", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position": "ASC"})
     *
     * @Assert\Valid
     *
     * @SymfonySerializer\Groups("survey_write_dc")
     */
    private $questions;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @SymfonySerializer\Groups(
     *     "survey_list_dc",
     *     "survey_read_dc",
     *     "survey_write_dc",
     *     "survey_update_dc"
     * )
     */
    private $published;

    public function __construct(UuidInterface $uuid = null, string $name = null, bool $published = false)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->name = $name;
        $this->published = $published;
        $this->questions = new ArrayCollection();
    }

    public function resetId(): void
    {
        $this->id = null;
    }

    public function refreshUuid(): void
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function addQuestion(SurveyQuestion $surveyQuestion): void
    {
        if (!$this->questions->contains($surveyQuestion)) {
            $surveyQuestion->setSurvey($this);
            $this->questions->add($surveyQuestion);
        }
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function setQuestions(Collection $surveyQuestions): void
    {
        $this->questions = $surveyQuestions;
    }

    public function removeQuestion(SurveyQuestion $surveyQuestion): void
    {
        $surveyQuestion->resetSurvey();
        $this->questions->removeElement($surveyQuestion);
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    /**
     * @SymfonySerializer\Groups({"survey_list_dc"})
     * @SymfonySerializer\SerializedName("nb_questions")
     */
    public function getQuestionsCount(): int
    {
        return $this->questions->count();
    }

    public function isLocal(): bool
    {
        return SurveyTypeEnum::LOCAL === $this->getType();
    }

    public function isNational(): bool
    {
        return SurveyTypeEnum::NATIONAL === $this->getType();
    }

    /**
     * @SymfonySerializer\Groups("survey_list", "survey_list_dc")
     */
    abstract public function getType(): string;

    /**
     * @SymfonySerializer\Groups("survey_list_dc", "survey_read_dc")
     */
    public function getCreator(): ?Adherent
    {
        return $this->getCreatedByAdherent();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->resetId();
            $this->refreshUuid();
            $this->setPublished(false);
            $this->setName($this->name.' (Copie)');

            $questions = new ArrayCollection();
            foreach ($this->getQuestions() as $surveyQuestion) {
                $clonedSurveyQuestion = clone $surveyQuestion;
                $clonedSurveyQuestion->setSurvey($this);

                $clonedQuestion = clone $surveyQuestion->getQuestion();
                $clonedSurveyQuestion->setQuestion($clonedQuestion);

                $questions->add($clonedSurveyQuestion);
            }

            $this->setQuestions($questions);
        }
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function isIndexable(): bool
    {
        return $this->isPublished();
    }
}
