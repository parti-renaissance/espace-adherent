<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Api\Doctrine\AuthoredItemsCollectionExtension;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Repository\AdherentMessageRepository;
use App\Scope\GeneralScopeGenerator;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class AdherentMessageScopeFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'scope';
    private const OPERATION_NAMES = ['get'];

    private GeneralScopeGenerator $generalScopeGenerator;
    private Security $security;
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
        $user = $this->security->getUser();

        if (
            (!$user instanceof Adherent)
            || !is_a($resourceClass, AbstractAdherentMessage::class, true)
            || self::PROPERTY_NAME !== $property
            || !\in_array($operationName, self::OPERATION_NAMES, true)
        ) {
            return;
        }

        $scopeGenerator = $this->generalScopeGenerator->getGenerator($value, $user);

        $author = $scopeGenerator->isDelegatedAccess()
            ? $scopeGenerator->getDelegatedAccess()->getDelegator()
            : $user
        ;

        $alias = $queryBuilder->getRootAliases()[0];

        $this
            ->adherentMessageRepository
            ->withMessageType($queryBuilder, $scopeGenerator->getCode(), $alias)
            ->withAuthor($queryBuilder, $author, $alias)
        ;

        $this->authoredItemsCollectionExtension->setSkip(true);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY_NAME => [
                'property' => null,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    /**
     * @required
     */
    public function setGeneralScopeGenerator(GeneralScopeGenerator $generalScopeGenerator): void
    {
        $this->generalScopeGenerator = $generalScopeGenerator;
    }

    /**
     * @required
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
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
