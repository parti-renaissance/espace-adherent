<?php

namespace App\Api\Filter;

use App\Api\Doctrine\AuthoredItemsCollectionExtension;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Repository\AdherentMessageRepository;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class AdherentMessageScopeFilter extends AbstractScopeFilter
{
    private AdherentMessageRepository $adherentMessageRepository;
    private AuthoredItemsCollectionExtension $authoredItemsCollectionExtension;

    protected function needApplyFilter(string $property, string $resourceClass, string $operationName = null): bool
    {
        return is_a($resourceClass, AbstractAdherentMessage::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $user = $scopeGenerator->isDelegatedAccess() ? $scopeGenerator->getDelegatedAccess()->getDelegator() : $currentUser;

        $this
            ->adherentMessageRepository
            ->withMessageType($queryBuilder, $scopeGenerator->getCode(), $alias)
            ->withAuthor($queryBuilder, $user, $alias)
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
