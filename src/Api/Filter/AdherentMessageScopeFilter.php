<?php

namespace App\Api\Filter;

use App\Api\Doctrine\AuthoredItemsCollectionExtension;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Repository\AdherentMessageRepository;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Service\Attribute\Required;

final class AdherentMessageScopeFilter extends AbstractScopeFilter
{
    private AdherentMessageRepository $adherentMessageRepository;
    private AuthoredItemsCollectionExtension $authoredItemsCollectionExtension;

    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, AdherentMessage::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator,
        string $resourceClass,
        array $context,
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $scope = $scopeGenerator->generate($currentUser);
        $user = $scope->getMainUser();

        $this
            ->adherentMessageRepository
            ->withInstanceScope($queryBuilder, $scope->getMainCode(), $alias)
            ->withAuthor($queryBuilder, $user, $alias)
        ;

        $this->authoredItemsCollectionExtension->setSkip(true);
    }

    #[Required]
    public function setAdherentMessageRepository(AdherentMessageRepository $adherentMessageRepository): void
    {
        $this->adherentMessageRepository = $adherentMessageRepository;
    }

    #[Required]
    public function setAuthoredItemsCollectionExtension(
        AuthoredItemsCollectionExtension $authoredItemsCollectionExtension,
    ): void {
        $this->authoredItemsCollectionExtension = $authoredItemsCollectionExtension;
    }
}
