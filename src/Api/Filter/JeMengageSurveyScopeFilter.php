<?php

declare(strict_types=1);

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
use Symfony\Contracts\Service\Attribute\Required;

final class JeMengageSurveyScopeFilter extends AbstractScopeFilter
{
    private ZoneRepository $zoneRepository;

    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, Survey::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator,
        string $resourceClass,
        array $context,
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $user = $scopeGenerator->isDelegatedAccess() ? $scopeGenerator->getDelegatedAccess()->getDelegator() : $currentUser;

        switch ($scopeGenerator->getCode()) {
            case ScopeEnum::NATIONAL:
            case ScopeEnum::PHONING_NATIONAL_MANAGER:
            case ScopeEnum::PAP_NATIONAL_MANAGER:
                $queryBuilder
                    ->andWhere(\sprintf('%s INSTANCE OF :national', $alias))
                    ->setParameter('national', SurveyTypeEnum::NATIONAL)
                ;
                break;
            case ScopeEnum::CORRESPONDENT:
                $queryBuilder
                    ->leftJoin(LocalSurvey::class, 'ls', Join::WITH, \sprintf('ls.id = %s.id', $alias))
                    ->leftJoin('ls.zone', 'zone')
                    ->leftJoin('zone.parents', 'parent')
                    ->andWhere(new Orx()
                        ->add(\sprintf('%s INSTANCE OF :national', $alias))
                        ->add(\sprintf('%s INSTANCE OF :local AND (zone = :zone OR parent IN (:zone))', $alias))
                    )
                    ->setParameter('national', SurveyTypeEnum::NATIONAL)
                    ->setParameter('local', SurveyTypeEnum::LOCAL)
                    ->setParameter('zone', $user->getCorrespondentZone())
                ;
                break;
            case ScopeEnum::LEGISLATIVE_CANDIDATE:
                $queryBuilder
                    ->leftJoin(LocalSurvey::class, 'ls', Join::WITH, \sprintf('ls.id = %s.id', $alias))
                    ->andWhere(\sprintf('%s INSTANCE OF :local AND ls.zone = :zone', $alias))
                    ->setParameter('local', SurveyTypeEnum::LOCAL)
                    ->setParameter('zone', $user->getLegislativeCandidateZone())
                ;
                break;
        }
    }

    #[Required]
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
