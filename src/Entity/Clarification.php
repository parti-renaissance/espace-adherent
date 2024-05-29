<?php

namespace App\Entity;

use App\Repository\ClarificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(fields={"slug"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
#[ORM\Table(name: 'clarifications')]
#[ORM\Entity(repositoryClass: ClarificationRepository::class)]
class Clarification implements EntityMediaInterface, EntityContentInterface, EntitySoftDeletedInterface, IndexableEntityInterface
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use EntityContentTrait;
    use EntityMediaTrait;
    use EntityPublishableTrait;

    /**
     * @var int
     */
    #[ORM\Column(type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function isIndexable(): bool
    {
        return $this->isPublished() && $this->isNotDeleted();
    }

    public function getIndexOptions(): array
    {
        return [];
    }
}
