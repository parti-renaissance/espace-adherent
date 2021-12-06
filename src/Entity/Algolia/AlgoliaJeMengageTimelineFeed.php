<?php

namespace App\Entity\Algolia;

use Algolia\SearchBundle\Entity\Aggregator;
use App\Entity\IndexableEntityInterface;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Pap\Campaign;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 *
 * @internal
 */
class AlgoliaJeMengageTimelineFeed extends Aggregator implements IndexableEntityInterface
{
    /**
     * @var UuidInterface|null
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    protected $id;

    public function __construct($entity, array $entityIdentifierValues)
    {
        parent::__construct($entity, $entityIdentifierValues);

        $this->id = Uuid::uuid4();
    }

    public static function getEntities()
    {
        return [
            LocalSurvey::class,
            NationalSurvey::class,
            Campaign::class,
        ];
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function isIndexable(): bool
    {
        if (!$this->entity instanceof IndexableEntityInterface) {
            throw new \LogicException(sprintf('Algolia Sub Entity "%s" should implement "%s"', \get_class($this->entity), IndexableEntityInterface::class));
        }

        return $this->entity->isIndexable();
    }
}
