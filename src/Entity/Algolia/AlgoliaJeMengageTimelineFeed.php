<?php

namespace App\Entity\Algolia;

use Algolia\SearchBundle\Entity\Aggregator;
use App\Entity\Action\Action;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\Event\Event;
use App\Entity\IndexableEntityInterface;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\News;
use App\Entity\Jecoute\Riposte;
use App\Entity\Pap\Campaign as PapCampaign;
use App\Entity\Phoning\Campaign as PhoningCampaign;
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
        return [
            LocalSurvey::class,
            NationalSurvey::class,
            PapCampaign::class,
            PhoningCampaign::class,
            News::class,
            Riposte::class,
            Event::class,
            Action::class,
            AdherentMessage::class,
        ];
    }

    public function isIndexable(): bool
    {
        if (!$this->entity instanceof IndexableEntityInterface) {
            throw new \LogicException(\sprintf('Algolia Sub Entity "%s" should implement "%s"', \get_class($this->entity), IndexableEntityInterface::class));
        }

        return $this->entity->isIndexable();
    }
}
