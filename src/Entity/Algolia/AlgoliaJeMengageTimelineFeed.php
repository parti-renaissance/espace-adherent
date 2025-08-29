<?php

namespace App\Entity\Algolia;

use Algolia\SearchBundle\Entity\Aggregator;
use App\Entity\IndexableEntityInterface;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @internal
 */
#[ORM\Entity]
class AlgoliaJeMengageTimelineFeed extends Aggregator implements IndexableEntityInterface
{
    /**
     * @var UuidInterface|null
     */
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    protected $objectID;

    public function __construct($entity, array $entityIdentifierValues)
    {
        parent::__construct($entity, $entityIdentifierValues);

        $this->objectID = $entity->getUuid()->toString();
    }

    public static function getEntities(): array
    {
        return array_keys(TimelineFeedTypeEnum::CLASS_MAPPING);
    }

    public function isIndexable(): bool
    {
        if (!$this->entity instanceof IndexableEntityInterface) {
            throw new \LogicException(\sprintf('Algolia Sub Entity "%s" should implement "%s"', \get_class($this->entity), IndexableEntityInterface::class));
        }

        return $this->entity->isIndexable();
    }
}
