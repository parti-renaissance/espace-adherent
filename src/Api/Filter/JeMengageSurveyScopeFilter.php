<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\Survey;
use App\Jecoute\SurveyTypeEnum;
use App\Repository\Geo\ZoneRepository;
use App\Scope\Generator\ScopeGeneratorInterface;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

final class JeMengageSurveyScopeFilter extends AbstractScopeFilter
{
    private ZoneRepository $zoneRepository;

    protected function needApplyFilter(string $property, string $resourceClass, string $operationName = null): bool
    {
        return is_a($resourceClass, Survey::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $user = $scopeGenerator->isDelegatedAccess() ? $scopeGenerator->getDelegatedAccess()->getDelegator() : $currentUser;

        switch ($scopeGenerator->getCode()) {
            case ScopeEnum::NATIONAL:
                $queryBuilder
                    ->andWhere(sprintf('%s INSTANCE OF :national', $alias))
                    ->setParameter('national', SurveyTypeEnum::NATIONAL)
                ;
                break;
            case ScopeEnum::REFERENT:
                $queryBuilder
                    ->leftJoin(LocalSurvey::class, 'ls', Join::WITH, sprintf('ls.id = %s.id', $alias))
                    ->orWhere((new Orx())
                        ->add(sprintf('%s INSTANCE OF :national', $alias))
                        ->add(sprintf('%s INSTANCE OF :local AND ls.zone IN (:zones)', $alias))
                    )
                    ->setParameter('national', SurveyTypeEnum::NATIONAL)
                    ->setParameter('local', SurveyTypeEnum::LOCAL)
                    ->setParameter('zones', $this->zoneRepository->findForJecouteByReferentTags($user->getManagedArea()->getTags()->toArray()))
                ;
                break;
            case ScopeEnum::CORRESPONDENT:
                $queryBuilder
                    ->leftJoin(LocalSurvey::class, 'ls', Join::WITH, sprintf('ls.id = %s.id', $alias))
                    ->orWhere((new Orx())
                        ->add(sprintf('%s INSTANCE OF :national', $alias))
                        ->add(sprintf('%s INSTANCE OF :local AND ls.zone IN (:zones)', $alias))
                    )
                    ->setParameter('national', SurveyTypeEnum::NATIONAL)
                    ->setParameter('local', SurveyTypeEnum::LOCAL)
                    ->setParameter('zones', array_merge([$user->getCorrespondentZone()], $user->getCorrespondentZone()->getChildren()))
                ;
                break;
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
