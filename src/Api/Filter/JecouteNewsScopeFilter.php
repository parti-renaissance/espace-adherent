<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Repository\AdherentMessageRepository;
use App\Repository\Geo\ZoneRepository;
use App\Scope\GeneralScopeGenerator;
use App\Scope\ScopeEnum;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class JecouteNewsScopeFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'scope';
    private const OPERATION_NAMES = ['get_private'];

    private GeneralScopeGenerator $generalScopeGenerator;
    private Security $security;
    private AdherentMessageRepository $adherentMessageRepository;
    private ZoneRepository $zoneRepository;

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
            || !is_a($resourceClass, News::class, true)
            || self::PROPERTY_NAME !== $property
            || !\in_array($operationName, self::OPERATION_NAMES, true)
        ) {
            return;
        }

        $scopeGenerator = $this->generalScopeGenerator->getGenerator($value, $user);
        $scope = $scopeGenerator->getCode();

        $author = $scopeGenerator->isDelegatedAccess()
            ? $scopeGenerator->getDelegatedAccess()->getDelegator()
            : $user
        ;

        $alias = $queryBuilder->getRootAliases()[0];

        if (ScopeEnum::NATIONAL === $scope) {
            $queryBuilder
                ->andWhere(sprintf('%s.space IS NULL', $alias))
            ;
        } elseif (ScopeEnum::REFERENT === $scope) {
            $queryBuilder
                ->andWhere(sprintf('%s.zone IN (:zones)', $alias))
                ->setParameter('zones', $this->zoneRepository->findForJecouteByReferentTags($author->getManagedArea()->getTags()->toArray()))
            ;
        }
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
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
