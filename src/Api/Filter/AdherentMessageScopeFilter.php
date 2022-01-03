<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Api\Doctrine\AuthoredItemsCollectionExtension;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Repository\AdherentMessageRepository;
use Doctrine\ORM\QueryBuilder;

final class AdherentMessageScopeFilter extends AbstractScopeFilter
{
    private AdherentMessageRepository $adherentMessageRepository;
    private AuthoredItemsCollectionExtension $authoredItemsCollectionExtension;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            !is_a($resourceClass, AbstractAdherentMessage::class, true)
            || !$this->needApplyFilter($property, $operationName)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $this
            ->adherentMessageRepository
            ->withMessageType($queryBuilder, $this->getScopeGenerator($value)->getCode(), $alias)
            ->withAuthor($queryBuilder, $this->getUser($value), $alias)
        ;

        $this->authoredItemsCollectionExtension->setSkip(true);
    }

    /**
     * @required
     */
    public function setAdherentMessageRepository(AdherentMessageRepository $adherentMessageRepository): void
    {
        $this->adherentMessageRepository = $adherentMessageRepository;
    }

    /**
     * @required
     */
    public function setAuthoredItemsCollectionExtension(
        AuthoredItemsCollectionExtension $authoredItemsCollectionExtension
    ): void {
        $this->authoredItemsCollectionExtension = $authoredItemsCollectionExtension;
    }
}
