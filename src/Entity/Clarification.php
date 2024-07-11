<?php

namespace App\Entity;

use App\Repository\ClarificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: ClarificationRepository::class)]
#[ORM\Table(name: 'clarifications')]
#[UniqueEntity(fields: ['slug'])]
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
    #[ORM\GeneratedValue]
    #[ORM\Id]
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
