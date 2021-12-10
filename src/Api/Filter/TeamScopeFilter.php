<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Api\Doctrine\AuthoredItemsCollectionExtension;
use App\Repository\AdherentMessageRepository;
use App\Scope\GeneralScopeGenerator;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class TeamScopeFilter extends AbstractContextAwareFilter
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
        echo 'hello world!';
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
