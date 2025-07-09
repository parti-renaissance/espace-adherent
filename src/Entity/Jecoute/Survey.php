<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Api\Filter\JeMengageSurveyScopeFilter;
use App\Api\Filter\SurveyTypeFilter;
use App\Controller\Api\Jecoute\GetSurveysKpiController;
use App\Entity\Adherent;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityScopeVisibilityInterface;
use App\Entity\EntityTimestampableTrait;
use App\Entity\IndexableEntityInterface;
use App\EntityListener\AlgoliaIndexListener;
use App\Jecoute\SurveyTypeEnum;
use App\Repository\Jecoute\SurveyRepository;
use App\Scope\ScopeVisibilityEnum;
use App\Validator\Jecoute\SurveyScopeTarget;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(filterClass: BooleanFilter::class, properties: ['published'])]
#[ApiFilter(filterClass: SurveyTypeFilter::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/surveys/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['survey_read_dc']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'survey') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new Put(
            uriTemplate: '/v3/surveys/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['survey_read_dc']],
            denormalizationContext: ['groups' => ['survey_write_dc']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'survey')"
        ),
        new GetCollection(
            uriTemplate: '/v3/surveys',
            paginationMaximumItemsPerPage: 1000,
            normalizationContext: ['groups' => ['survey_list_dc']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', ['survey', 'phoning_campaign', 'pap_v2'])"
        ),
        new Post(
            uriTemplate: '/v3/surveys',
            normalizationContext: ['groups' => ['survey_read_dc']],
            denormalizationContext: ['groups' => ['survey_write_dc']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'survey')"
        ),
        new GetCollection(
            uriTemplate: '/v3/surveys/kpi',
            controller: GetSurveysKpiController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'survey')"
        ),
    ],
    normalizationContext: ['groups' => ['survey_list']],
    filters: [JeMengageSurveyScopeFilter::class],
    order: ['createdAt' => 'DESC']
)]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([SurveyTypeEnum::LOCAL => LocalSurvey::class, SurveyTypeEnum::NATIONAL => NationalSurvey::class])]
#[ORM\Entity(repositoryClass: SurveyRepository::class)]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\Table(name: 'jecoute_survey')]
#[SurveyScopeTarget]
abstract class Survey implements IndexableEntityInterface, EntityAdministratorBlameableInterface, EntityAdherentBlameableInterface, EntityScopeVisibilityInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;

    #[Assert\Length(max: 70)]
    #[Assert\NotBlank]
    #[Groups(['survey_list', 'survey_list_dc', 'survey_write_dc', 'survey_read_dc', 'phoning_campaign_read', 'phoning_campaign_history_read_list', 'phoning_campaign_replies_list', 'pap_campaign_replies_list', 'pap_campaign_history_read_list'])]
    #[ORM\Column]
    private $name;

    /**
     * @var SurveyQuestion[]|Collection
     */
    #[Assert\Count(min: 1, minMessage: 'survey.questions.min_count')]
    #[Assert\Valid]
    #[Groups(['survey_write_dc'])]
    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyQuestion::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private $questions;

    #[Groups(['survey_list_dc', 'survey_read_dc', 'survey_write_dc'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $published;

    public function __construct(?UuidInterface $uuid = null, ?string $name = null, bool $published = false)
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

    #[Groups(['survey_list_dc'])]
    #[SerializedName('nb_questions')]
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

    #[Groups(['survey_list', 'survey_list_dc', 'survey_read_dc'])]
    abstract public function getType(): string;

    #[Groups(['survey_list_dc', 'survey_read_dc'])]
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

    public function isIndexable(): bool
    {
        return $this->isPublished();
    }

    public function getVisibility(): string
    {
        return SurveyTypeEnum::LOCAL === $this->getType() ? ScopeVisibilityEnum::LOCAL : ScopeVisibilityEnum::NATIONAL;
    }

    public function isNationalVisibility(): bool
    {
        return ScopeVisibilityEnum::NATIONAL === $this->getVisibility();
    }
}
