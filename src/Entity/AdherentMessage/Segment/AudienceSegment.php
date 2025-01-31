<?php

namespace App\Entity\AdherentMessage\Segment;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
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
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/audience-segments/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('ROLE_MESSAGE_REDACTOR') and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages'))"
        ),
        new Put(
            uriTemplate: '/v3/audience-segments/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('ROLE_MESSAGE_REDACTOR') and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages'))"
        ),
        new Delete(
            uriTemplate: '/v3/audience-segments/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('ROLE_MESSAGE_REDACTOR') and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages'))"
        ),
        new Post(
            uriTemplate: '/v3/audience-segments',
            security: "is_granted('ROLE_MESSAGE_REDACTOR')"
        ),
    ],
    normalizationContext: ['groups' => ['audience_segment_read']],
    denormalizationContext: ['groups' => ['audience_segment_write'], 'disable_type_enforcement' => true]
)]
#[ORM\Entity(repositoryClass: AudienceSegmentRepository::class)]
class AudienceSegment implements AuthorInterface, DynamicSegmentInterface
{
    use EntityIdentityTrait;
    use DynamicSegmentTrait;
    use AuthoredTrait;

    /**
     * @var AudienceFilter|null
     */
    #[Assert\NotNull]
    #[Assert\Valid]
    #[Groups(['audience_segment_read', 'audience_segment_write'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(targetEntity: AudienceFilter::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    private $filter;

    /**
     * @var Adherent|null
     */
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
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
