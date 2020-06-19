<?php

namespace App\Repository\ElectedRepresentative;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\ElectedRepresentative\Filter\ListFilter;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\UserListDefinitionEnum;
use App\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ElectedRepresentativeRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ElectedRepresentative::class);
    }

    /**
     * @return ElectedRepresentative[]|PaginatorInterface
     */
    public function searchByFilter(ListFilter $filter, int $page = 1, int $limit = 100): PaginatorInterface
    {
        return $this->configurePaginator(
            $this->createFilterQueryBuilder($filter),
            $page,
            $limit,
            static function (Query $query) {
                $query
                    ->useResultCache(true)
                    ->setResultCacheLifetime(1800)
                ;
            }
        );
    }

    public function countForReferentTags(array $referentTags): int
    {
        $qb = $this
            ->createQueryBuilder('er')
            ->select('COUNT(DISTINCT er.id)')
        ;
        $this->withActiveMandatesCondition($qb);

        return (int) $this
            ->withZoneCondition($qb, $referentTags)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function isInReferentManagedArea(ElectedRepresentative $electedRepresentative, array $referentTags): bool
    {
        $qb = $this
            ->createQueryBuilder('er')
        ;
        $this->withActiveMandatesCondition($qb);

        $res = $this
            ->withZoneCondition($qb, $referentTags)
            ->andWhere('er = :electedRepresentative')
            ->setParameter('electedRepresentative', $electedRepresentative)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return null !== $res;
    }

    private function createFilterQueryBuilder(ListFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('er')
            ->orderBy('er.'.$filter->getSort(), 'd' === $filter->getOrder() ? 'DESC' : 'ASC')
        ;

        $this->withActiveMandatesCondition($qb);
        $this->withZoneCondition($qb, $filter->getReferentTags());

        $qb
            ->addSelect('mandate', 'zone', 'politicalFunction', 'userListDefinition', 'label')
            ->addSelect('sponsorship', 'socialNetworkLink', 'userListDefinition')
            ->leftJoin('er.labels', 'label')
            ->leftJoin('er.sponsorships', 'sponsorship')
            ->leftJoin('er.socialNetworkLinks', 'socialNetworkLink')
            ->leftJoin('er.politicalFunctions', 'politicalFunction')
            ->leftJoin('er.userListDefinitions', 'userListDefinition')
        ;

        if ($lastName = $filter->getLastName()) {
            $qb
                ->andWhere('er.lastName LIKE :last_name')
                ->setParameter('last_name', '%'.$lastName.'%')
            ;
        }

        if ($firstName = $filter->getFirstName()) {
            $qb
                ->andWhere('er.firstName LIKE :first_name')
                ->setParameter('first_name', '%'.$firstName.'%')
            ;
        }

        if ($gender = $filter->getGender()) {
            $qb
                ->andWhere('er.gender = :gender')
                ->setParameter('gender', $gender)
            ;
        }

        if ($labels = $filter->getLabels()) {
            $qb
                ->andWhere('label.name in (:labels)')
                ->andWhere('label.onGoing = 1')
                ->andWhere('label.finishYear IS NULL')
                ->setParameter('labels', $labels)
            ;
        }

        if ($mandates = $filter->getMandates()) {
            $qb
                ->andWhere('mandate.type in (:mandates)')
                ->setParameter('mandates', $mandates)
            ;
        }

        if ($politicalFunctions = $filter->getPoliticalFunctions()) {
            $qb
                ->andWhere('politicalFunction.name in (:politicalFunctions)')
                ->andWhere('politicalFunction.onGoing = 1')
                ->andWhere('politicalFunction.finishAt IS NULL')
                ->setParameter('politicalFunctions', $politicalFunctions)
            ;
        }

        if ($cities = $filter->getCities()) {
            $qb
                ->andWhere('mandate.zone in (:cities)')
                ->setParameter('cities', $cities)
            ;
        }

        if ($userListDefinitions = $filter->getUserListDefinitions()) {
            $qb
                ->andWhere('userListDefinition.id in (:userListDefinitions)')
                ->andWhere('userListDefinition.type = :erType')
                ->setParameter('userListDefinitions', $userListDefinitions)
                ->setParameter('erType', UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE)
            ;
        }

        $isAdherent = $filter->isAdherent();
        if ($isAdherent) {
            $qb->andWhere('er.isAdherent = 1');
        } elseif (false === $isAdherent) {
            $qb->andWhere('er.isAdherent = 0');
        }

        return $qb;
    }

    private function withActiveMandatesCondition(QueryBuilder $qb, string $alias = 'er'): QueryBuilder
    {
        return $qb
            ->leftJoin($alias.'.mandates', 'mandate')
            ->andWhere('mandate.finishAt IS NULL')
            ->andWhere('mandate.onGoing = 1')
            ->andWhere('mandate.isElected = 1')
        ;
    }

    private function withZoneCondition(QueryBuilder $qb, array $referentTags, string $alias = 'er'): QueryBuilder
    {
        if (!\in_array('mandate', $qb->getAllAliases(), true)) {
            $qb->leftJoin($alias.'.mandates', 'mandate');
        }

        $hasParis = false;
        foreach ($referentTags as $tag) {
            if (0 === mb_strpos($tag->getCode(), '750') || 0 === mb_strpos($tag->getCode(), 'CIRCO_750')) {
                $hasParis = true;

                break;
            }
        }

        $zoneCondition = new Orx();
        $zoneCondition->add('tag IN (:tags)');
        $qb->setParameter('tags', $referentTags);
        // if referent has some Paris tag, we should return elected representatives of all Paris zones
        if ($hasParis) {
            $zoneCondition->add('tag.code LIKE :paris_arr OR tag.code LIKE :paris_circo');
            $qb->setParameter('paris_arr', '750%');
            $qb->setParameter('paris_circo', 'CIRCO\_750%');
        }

        $qb
            ->leftJoin('mandate.zone', 'zone')
            ->leftJoin('zone.referentTags', 'tag')
            ->andWhere($zoneCondition)
        ;

        return $qb;
    }
}
