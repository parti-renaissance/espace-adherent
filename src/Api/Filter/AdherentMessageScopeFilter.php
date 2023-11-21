<?php

namespace App\Api\Filter;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Api\Doctrine\AuthoredItemsCollectionExtension;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
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
        return is_a($resourceClass, AbstractAdherentMessage::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator,
        string $resourceClass,
        array $context
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $user = $scopeGenerator->isDelegatedAccess() ? $scopeGenerator->getDelegatedAccess()->getDelegator() : $currentUser;

        $type = AdherentMessageTypeEnum::getMessageTypeFromScopeCode($scopeGenerator->getCode());

        if (isset($context['filters']['statutory']) && '1' === $context['filters']['statutory']) {
            $type = AdherentMessageTypeEnum::STATUTORY;
        }

        $this
            ->adherentMessageRepository
            ->withMessageType($queryBuilder, $type, $alias)
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
        AuthoredItemsCollectionExtension $authoredItemsCollectionExtension
    ): void {
        $this->authoredItemsCollectionExtension = $authoredItemsCollectionExtension;
    }
}
