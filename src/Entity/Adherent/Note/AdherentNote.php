<?php

declare(strict_types=1);

namespace App\Entity\Adherent\Note;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Api\Processor\AdherentNotePostProcessor;
use App\Api\Processor\AdherentNotePutProcessor;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Normalizer\ImageExposeNormalizer;
use App\Repository\Adherent\Note\AdherentNoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/adherents/{uuid}/notes',
            uriVariables: [
                'uuid' => new Link(
                    toProperty: 'targetAdherent',
                    fromClass: Adherent::class,
                ),
            ],
            requirements: ['uuid' => '%pattern_uuid%'],
            paginationItemsPerPage: 20,
            order: ['createdAt' => 'DESC'],
            normalizationContext: ['groups' => ['adherent_note:read', 'adherent_note_author:read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
            security: 'is_granted("REQUEST_SCOPE_GRANTED", "adherent_notes")',
        ),
        new Post(
            uriTemplate: '/adherents/{uuid}/notes',
            uriVariables: [
                'uuid' => new Link(
                    toProperty: 'targetAdherent',
                    fromClass: Adherent::class,
                ),
            ],
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['adherent_note:read', 'adherent_note_author:read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
            denormalizationContext: ['groups' => ['adherent_note:write']],
            security: 'is_granted("REQUEST_SCOPE_GRANTED", "adherent_notes")',
            processor: AdherentNotePostProcessor::class,
        ),
        new Put(
            uriTemplate: '/adherents/{adherentUuid}/notes/{uuid}',
            uriVariables: [
                'adherentUuid' => new Link(
                    toProperty: 'targetAdherent',
                    fromClass: Adherent::class,
                ),
                'uuid' => new Link(fromClass: AdherentNote::class),
            ],
            requirements: ['adherentUuid' => '%pattern_uuid%', 'uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['adherent_note:read', 'adherent_note_author:read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
            denormalizationContext: ['groups' => ['adherent_note:write']],
            security: 'is_granted("REQUEST_SCOPE_GRANTED", "adherent_notes")',
            processor: AdherentNotePutProcessor::class,
        ),
    ],
    routePrefix: '/v3',
)]
#[ORM\Entity(repositoryClass: AdherentNoteRepository::class)]
class AdherentNote
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public Adherent $targetAdherent;

    #[Assert\Length(max: 10000)]
    #[Assert\NotBlank]
    #[Groups(['adherent_note:read', 'adherent_note:write'])]
    #[ORM\Column(type: 'text')]
    public string $content;

    #[ORM\OneToMany(mappedBy: 'note', targetEntity: AdherentNoteAuthor::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['editedAt' => 'ASC'])]
    private Collection $authors;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->authors = new ArrayCollection();
    }

    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    #[Groups(['adherent_note:read'])]
    public function getAuthor(): ?AdherentNoteAuthor
    {
        $first = $this->authors->filter(fn(AdherentNoteAuthor $a) => AdherentNoteAuthor::TYPE_ADD === $a->type)->first();

        return $first ?: null;
    }

    #[Groups(['adherent_note:read'])]
    public function getLastEditor(): ?AdherentNoteAuthor
    {
        $last = $this->authors->last();

        return $last ?: null;
    }

    #[Groups(['adherent_note:read'])]
    public function isModifiable(): bool
    {
        return $this->createdAt > new \DateTimeImmutable('-7 days');
    }
}
