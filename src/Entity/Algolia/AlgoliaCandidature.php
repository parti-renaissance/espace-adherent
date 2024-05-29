<?php

namespace App\Entity\Algolia;

use Algolia\SearchBundle\Entity\Aggregator;
use App\Entity\AlgoliaIndexedEntityInterface;
use App\Entity\CommitteeCandidacy;
use App\Entity\TerritorialCouncil\Candidacy;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @internal
 */
#[ORM\Entity]
class AlgoliaCandidature extends Aggregator implements AlgoliaIndexedEntityInterface
{
    /**
     * @var UuidInterface|null
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected $id;

    public function __construct($entity, array $entityIdentifierValues)
    {
        parent::__construct($entity, $entityIdentifierValues);

        $this->id = Uuid::uuid4();
    }

    public static function getEntities()
    {
        return [
            CommitteeCandidacy::class,
            Candidacy::class,
        ];
    }

    public function getIndexOptions(): array
    {
        return [];
    }
}
