<?php

namespace App\Api\Filter;

use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Repository\AdherentMessageRepository;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Service\Attribute\Required;

final class AdherentMessageScopeFilter extends AbstractScopeFilter
{
    private AdherentMessageRepository $adherentMessageRepository;

    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return AdherentMessage::class === $resourceClass;
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

        $this
            ->adherentMessageRepository
            ->withInstanceScope($queryBuilder, $scope->getMainCode(), $alias)
            ->withSource(
                $queryBuilder,
                ($context[PrivatePublicContextBuilder::CONTEXT_KEY] ?? null) === PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER ?
                    AdherentMessageInterface::SOURCE_VOX : AdherentMessageInterface::SOURCE_CADRE,
                $alias
            )
        ;
    }

    #[Required]
    public function setAdherentMessageRepository(AdherentMessageRepository $adherentMessageRepository): void
    {
        $this->adherentMessageRepository = $adherentMessageRepository;
    }
}
