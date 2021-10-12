<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Repository\AdherentMessageRepository;
use App\Scope\GeneralScopeGenerator;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;

final class AdherentMessageScopeFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'scope';
    private const OPERATION_NAMES = ['get'];

    private GeneralScopeGenerator $generalScopeGenerator;
    private Security $security;
    private AdherentMessageRepository $adherentMessageRepository;

    public function __construct(
        ManagerRegistry $managerRegistry,
        $requestStack = null,
        LoggerInterface $logger = null,
        array $properties = null,
        GeneralScopeGenerator $generalScopeGenerator,
        Security $security,
        AdherentMessageRepository $adherentMessageRepository
    ) {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties);

        $this->generalScopeGenerator = $generalScopeGenerator;
        $this->security = $security;
        $this->adherentMessageRepository = $adherentMessageRepository;
    }

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
            || AbstractAdherentMessage::class !== $resourceClass
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
}
