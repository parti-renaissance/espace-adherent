<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\Survey;
use App\Jecoute\SurveyTypeEnum;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

final class JeMengageSurveyScopeFilter extends AbstractScopeFilter
{
    private ZoneRepository $zoneRepository;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            (!$this->getUser($value) instanceof Adherent)
            || !is_a($resourceClass, Survey::class, true)
            || !$this->needApplyFilter($property, $operationName)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        if (ScopeEnum::NATIONAL === $this->getScopeGenerator($value)->getCode()) {
            $queryBuilder
                ->andWhere(sprintf('%s INSTANCE OF :national', $alias))
                ->setParameter('national', SurveyTypeEnum::NATIONAL)
            ;
        } elseif (ScopeEnum::REFERENT === $this->getScopeGenerator($value)->getCode()) {
            $or = new Orx();
            $or
                ->add(sprintf('%s INSTANCE OF :national', $alias))
                ->add(sprintf('%1$s INSTANCE OF :local AND ls.zone IN (:zones)', $alias))
            ;
            $queryBuilder
                ->leftJoin(LocalSurvey::class, 'ls', Join::WITH, sprintf('ls.id = %s.id', $alias))
                ->orWhere($or)
                ->setParameters([
                    'national' => SurveyTypeEnum::NATIONAL,
                    'local' => SurveyTypeEnum::LOCAL,
                    'zones' => $this->zoneRepository->findForJecouteByReferentTags($this->getUser($value)->getManagedArea()->getTags()->toArray()),
                ])
            ;
        }
    }

    /**
     * @required
     */
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
