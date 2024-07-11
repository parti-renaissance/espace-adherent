<?php

namespace App\Entity\AdherentMessage\Segment;

use ApiPlatform\Core\Annotation\ApiResource;
use App\AdherentMessage\DynamicSegmentInterface;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AuthoredTrait;
use App\Entity\AuthorInterface;
use App\Entity\DynamicSegmentTrait;
use App\Entity\EntityIdentityTrait;
use App\Repository\AdherentMessage\Segment\AudienceSegmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AudienceSegmentRepository::class)]
#[ApiResource(attributes: ['normalization_context' => ['groups' => ['audience_segment_read']], 'denormalization_context' => ['groups' => ['audience_segment_write'], 'disable_type_enforcement' => true]], collectionOperations: ['post' => ['path' => '/v3/audience-segments', 'security' => "is_granted('ROLE_MESSAGE_REDACTOR')"]], itemOperations: ['get' => ['path' => '/v3/audience-segments/{uuid}', 'requirements' => ['uuid' => '%pattern_uuid%'], 'security' => "is_granted('ROLE_MESSAGE_REDACTOR') and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages'))"], 'put' => ['path' => '/v3/audience-segments/{uuid}', 'requirements' => ['uuid' => '%pattern_uuid%'], 'security' => "is_granted('ROLE_MESSAGE_REDACTOR') and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages'))"], 'delete' => ['path' => '/v3/audience-segments/{uuid}', 'requirements' => ['uuid' => '%pattern_uuid%'], 'security' => "is_granted('ROLE_MESSAGE_REDACTOR') and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages'))"]])]
class AudienceSegment implements AuthorInterface, DynamicSegmentInterface
{
    use EntityIdentityTrait;
    use DynamicSegmentTrait;
    use AuthoredTrait;

    /**
     * @var AudienceFilter|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: AudienceFilter::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    #[Assert\Valid]
    #[Assert\NotNull]
    private $filter;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    #[Assert\NotBlank]
    protected $author;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getFilter(): ?AudienceFilter
    {
        return $this->filter;
    }

    public function setFilter(?AudienceFilter $filter): void
    {
        $this->filter = $filter;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(?Adherent $author): void
    {
        $this->author = $author;
    }
}
