<?php

namespace App\Repository;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Assessor\Filter\AssociationVotePlaceFilter;
use App\Entity\Adherent;
use App\Entity\AssessorOfficeEnum;
use App\Entity\AssessorRequest;
use App\Entity\VotePlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @deprecated {@see \App\Repository\Election\VotePlaceRepository}
 */
class VotePlaceRepository extends ServiceEntityRepository
{
    use GeoFilterTrait;
    use PaginatorTrait;
    use AssessorLocationTrait;

    public const ALIAS = 'vp';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VotePlace::class);
    }

    public static function addAndWhereOfficeAvailability(
        QueryBuilder $qb,
        AssessorRequest $assessorRequest,
        $alias = self::ALIAS
    ): QueryBuilder {
        if (AssessorOfficeEnum::HOLDER === $assessorRequest->getOffice()) {
            $qb->andWhere($alias.'.holderOfficeAvailable = true');
        } else {
            $qb->andWhere($alias.'.substituteOfficeAvailable = true');
        }

        return $qb;
    }

    private static function addAndWhereManagedBy(QueryBuilder $qb, Adherent $assessorManager): QueryBuilder
    {
        if ($assessorManager->getAssessorManagedArea()->getCodes() === ['ALL']) {
            return $qb;
        }

        $codesFilter = $qb->expr()->orX();

        foreach ($assessorManager->getAssessorManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->like(self::ALIAS.'.postalCode', ':code'.$key)
                );
                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq(self::ALIAS.'.country', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        return $qb->andWhere($codesFilter);
    }

    /**
     * @return VotePlace[]|PaginatorInterface
     */
    public function findAllForFilter(AssociationVotePlaceFilter $filter, int $page, int $limit): PaginatorInterface
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        if ($tags = $filter->getTags()) {
            $this->applyGeoFilter($qb, $tags, self::ALIAS, self::ALIAS.'.country', self::ALIAS.'.postalCode');
        }

        if ($inseeCodes = $filter->getInseeCodes()) {
            $qb
                ->andWhere('SUBSTRING_INDEX('.self::ALIAS.'.code, \'_\', 1) IN (:insee_codes)')
                ->setParameter('insee_codes', $inseeCodes)
            ;
        }

        if ($postalCodes = $filter->getPostalCodes()) {
            $orx = new Orx();

            foreach ($postalCodes as $index => $postalCode) {
                $orx->add(sprintf('FIND_IN_SET(:postal_code_%s, %s.postalCode) > 0', $index, self::ALIAS));
                $qb->setParameter('postal_code_'.$index, $postalCode);
            }

            $qb->andWhere($orx);
        }

        if ($city = $filter->getCity()) {
            $qb
                ->andWhere(self::ALIAS.'.city LIKE :city')
                ->setParameter('city', sprintf('%s%%', $city))
            ;
        }

        if ($country = $filter->getCountry()) {
            $qb
                ->andWhere(self::ALIAS.'.country = :country')
                ->setParameter('country', $country)
            ;
        }

        if ($name = $filter->getName()) {
            $qb
                ->andWhere(sprintf('%s.name LIKE :name OR %s.alias LIKE :name', self::ALIAS, self::ALIAS))
                ->setParameter('name', sprintf('%%%s%%', $name))
            ;
        }

        $qb
            ->orderBy(self::ALIAS.'.city', 'ASC')
            ->addOrderBy(self::ALIAS.'.name', 'ASC')
        ;

        return $this->configurePaginator($qb, $page, $limit);
    }

    public function findLastByCodePrefix(string $codePrefix): ?VotePlace
    {
        return $this->createQueryBuilder('vp')
            ->where('vp.code LIKE :code')
            ->setParameter('code', $codePrefix.'_%')
            ->setMaxResults(1)
            ->orderBy('vp.code', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
