<?php

namespace App\Entity\Action;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Action\ActionTypeEnum;
use App\Collection\ZoneCollection;
use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
use App\Entity\AuthorInstanceTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Entity\IndexableEntityInterface;
use App\Entity\ZoneableEntity;
use App\EntityListener\AlgoliaIndexListener;
use App\Geocoder\GeoPointInterface;
use App\Repository\Action\ActionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'vox_action')]
#[ORM\Entity(repositoryClass: ActionRepository::class)]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
#[ApiResource(attributes: ['denormalization_context' => ['groups' => ['action_write']], 'normalization_context' => ['groups' => ['action_read']], 'pagination_maximum_items_per_page' => 300, 'pagination_items_per_page' => 300], itemOperations: ['get' => ['path' => '/v3/actions/{uuid}'], 'put' => ['path' => '/v3/actions/{uuid}', 'security' => "object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'actions')"], 'cancel' => ['path' => '/v3/actions/{uuid}/cancel', 'method' => 'PUT', 'requirements' => ['uuid' => '%pattern_uuid%'], 'defaults' => ['_api_receive' => false], 'controller' => 'App\Controller\Api\Action\CancelActionController'], 'register' => ['path' => '/v3/actions/{uuid}/register', 'method' => 'POST', 'requirements' => ['uuid' => '%pattern_uuid%'], 'defaults' => ['_api_receive' => false], 'controller' => 'App\Controller\Api\Action\RegisterController'], 'unregister' => ['path' => '/v3/actions/{uuid}/register', 'method' => 'DELETE', 'requirements' => ['uuid' => '%pattern_uuid%'], 'defaults' => ['_api_receive' => false], 'controller' => 'App\Controller\Api\Action\RegisterController']], collectionOperations: ['get' => ['path' => '/v3/actions', 'normalization_context' => ['groups' => ['action_read_list']]], 'post' => ['path' => '/v3/actions']])]
#[ApiFilter(SearchFilter::class, properties: ['type' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['date'])]
class Action implements AuthorInstanceInterface, GeoPointInterface, ZoneableEntity, IndexableEntityInterface
{
    use EntityIdentityTrait;
    use EntityPostAddressTrait;
    use EntityZoneTrait;
    use EntityTimestampableTrait;
    use AuthorInstanceTrait;

    public const string STATUS_SCHEDULED = 'scheduled';
    public const string STATUS_CANCELLED = 'cancelled';

    #[Groups(['action_read', 'action_read_list', 'action_write'])]
    #[ORM\Column(name: '`type`')]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [ActionTypeEnum::class, 'toArray'])]
    public ?string $type = null;

    #[Groups(['action_read', 'action_read_list', 'action_write'])]
    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
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

    public function getIndexOptions(): array
    {
        return [];
    }

    public function isIndexable(): bool
    {
        return !$this->isCancelled();
    }
}
