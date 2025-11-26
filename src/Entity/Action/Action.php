<?php

namespace App\Entity\Action;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Action\ActionTypeEnum;
use App\Api\Filter\MySubscribedActionsFilter;
use App\Collection\ZoneCollection;
use App\Controller\Api\Action\CancelActionController;
use App\Controller\Api\Action\RegisterController;
use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
use App\Entity\AuthorInstanceTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Entity\IndexableEntityInterface;
use App\Entity\NotificationObjectInterface;
use App\Entity\ZoneableEntityInterface;
use App\EntityListener\AlgoliaIndexListener;
use App\Geocoder\GeoPointInterface;
use App\JeMengage\Hit\HitTargetInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\Normalizer\ImageExposeNormalizer;
use App\Repository\Action\ActionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['type' => 'exact'])]
#[ApiFilter(filterClass: DateFilter::class, properties: ['date'])]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['date'])]
#[ApiFilter(filterClass: MySubscribedActionsFilter::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/actions/{uuid}',
            normalizationContext: ['groups' => ['action_read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
        ),
        new Put(
            uriTemplate: '/v3/actions/{uuid}',
            security: "object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'actions')"
        ),
        new HttpOperation(
            method: 'PUT',
            uriTemplate: '/v3/actions/{uuid}/cancel',
            deserialize: false,
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: CancelActionController::class
        ),
        new HttpOperation(
            method: 'POST|DELETE',
            uriTemplate: '/v3/actions/{uuid}/register',
            deserialize: false,
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: RegisterController::class
        ),
        new GetCollection(
            uriTemplate: '/v3/actions',
            normalizationContext: ['groups' => ['action_read_list', ImageExposeNormalizer::NORMALIZATION_GROUP]]
        ),
        new Post(uriTemplate: '/v3/actions'),
    ],
    normalizationContext: ['groups' => ['action_read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
    denormalizationContext: ['groups' => ['action_write']],
    order: ['date' => 'ASC'],
    paginationItemsPerPage: 300,
    paginationMaximumItemsPerPage: 300,
)]
#[ORM\Entity(repositoryClass: ActionRepository::class)]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
#[ORM\Table(name: 'vox_action')]
class Action implements AuthorInstanceInterface, GeoPointInterface, ZoneableEntityInterface, IndexableEntityInterface, NotificationObjectInterface, HitTargetInterface
{
    use EntityIdentityTrait;
    use EntityPostAddressTrait;
    use EntityZoneTrait;
    use EntityTimestampableTrait;
    use AuthorInstanceTrait;

    public const string STATUS_SCHEDULED = 'scheduled';
    public const string STATUS_CANCELLED = 'cancelled';

    #[Assert\Choice(callback: [ActionTypeEnum::class, 'toArray'])]
    #[Assert\NotBlank]
    #[Groups(['action_read', 'action_read_list', 'action_write'])]
    #[ORM\Column(name: '`type`')]
    public ?string $type = null;

    #[Assert\NotBlank]
    #[Groups(['action_read', 'action_read_list', 'action_write'])]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $date = null;

    #[Groups(['action_read', 'action_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[Groups(['action_read'])]
    #[ORM\OneToMany(mappedBy: 'action', targetEntity: ActionParticipant::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private Collection $participants;

    /**
     * @var ZoneCollection|Zone[]
     */
    #[Groups(['action_read'])]
    #[ORM\JoinTable(name: 'vox_action_zone')]
    #[ORM\ManyToMany(targetEntity: Zone::class)]
    protected Collection $zones;

    #[Groups(['action_read', 'action_read_list'])]
    #[ORM\Column(options: ['default' => 'scheduled'])]
    public string $status = self::STATUS_SCHEDULED;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $canceledAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $notifiedAtFirstNotification = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $notifiedAtSecondNotification = null;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->participants = new ArrayCollection();
        $this->zones = new ZoneCollection();
    }

    #[Groups(['action_read_list'])]
    public function getParticipantsCount(): int
    {
        return $this->participants->count();
    }

    #[Groups(['action_read_list'])]
    public function getFirstParticipants(): array
    {
        return array_values($this->participants->matching(Criteria::create()->setMaxResults(3)->orderBy(['createdAt' => 'ASC']))->toArray());
    }

    public function getParticipants(): array
    {
        return $this->participants->toArray();
    }

    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->canceledAt = new \DateTime();
    }

    public function isCancelled(): bool
    {
        return self::STATUS_CANCELLED === $this->status;
    }

    public function addNewParticipant(Adherent $adherent): void
    {
        $this->participants->add(new ActionParticipant($this, $adherent));
    }

    public function isIndexable(): bool
    {
        return !$this->isCancelled();
    }

    public function isNotificationEnabled(SendNotificationCommandInterface $command): bool
    {
        return !$this->isCancelled();
    }

    public function handleNotificationSent(SendNotificationCommandInterface $command): void
    {
    }

    public function isNational(): bool
    {
        return false;
    }
}
