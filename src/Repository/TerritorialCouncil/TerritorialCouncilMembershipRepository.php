<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\Committee;
use App\Entity\ElectedRepresentative\Zone;
use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use App\TerritorialCouncil\Candidacy\SearchAvailableMembershipFilter;
use App\TerritorialCouncil\Filter\MembersListFilter;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TerritorialCouncilMembershipRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TerritorialCouncilMembership::class);
    }

    /**
     * @return TerritorialCouncilMembership[]
     */
    public function findAvailableMemberships(Candidacy $candidacy, SearchAvailableMembershipFilter $filter): array
    {
        $membership = $candidacy->getMembership();

        $qb = $this
            ->createQueryBuilder('membership')
            ->addSelect('adherent', 'quality')
            ->innerJoin('membership.qualities', 'quality')
            ->innerJoin('membership.adherent', 'adherent')
            ->leftJoin('membership.candidacies', 'candidacy', Join::WITH, 'candidacy.membership = membership AND candidacy.election = :election')
            ->where('membership.territorialCouncil = :council')
            ->andWhere('candidacy IS NULL OR candidacy.status = :candidacy_draft_status')
            ->andWhere('quality.name = :quality')
            ->andWhere('membership.id != :membership_id')
            ->andWhere(sprintf('membership.id NOT IN (%s)',
                $this->createQueryBuilder('t1')
                    ->select('t1.id')
                    ->innerJoin('t1.qualities', 't2')
                    ->where('t1.territorialCouncil = :council')
                    ->andWhere('t2.name IN (:qualities)')
                    ->getDQL()
            ))
            ->andWhere('adherent.gender = :gender')
            ->setParameters([
                'candidacy_draft_status' => Candidacy::STATUS_DRAFT,
                'election' => $candidacy->getElection(),
                'council' => $membership->getTerritorialCouncil(),
                'quality' => $filter->getQuality(),
                'membership_id' => $membership->getId(),
                'gender' => $candidacy->isFemale() ? Genders::MALE : Genders::FEMALE,
                'qualities' => TerritorialCouncilQualityEnum::FORBIDDEN_TO_CANDIDATE,
            ])
            ->orderBy('adherent.lastName')
            ->addOrderBy('adherent.firstName')
        ;

        if ($filter->getQuery()) {
            $qb
                ->andWhere('(adherent.firstName LIKE :query OR adherent.lastName LIKE :query)')
                ->setParameter('query', sprintf('%s%%', $filter->getQuery()))
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function searchByFilter(MembersListFilter $filter, int $page = 1, ?int $limit = 50): iterable
    {
        if ($limit) {
            return $this->configurePaginator($this->createFilterQueryBuilder($filter), $page, $limit);
        }

        return $this->createFilterQueryBuilder($filter)->getQuery()->getResult();
    }

    public function countForReferentTags(array $referentTags): int
    {
        $qb = $this
            ->createQueryBuilder('tcm')
            ->select('COUNT(1)')
            ->innerJoin('tcm.territorialCouncil', 'territorial_council')
        ;

        return (int) $this
            ->bindReferentTagsCondition($qb, $referentTags)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getForExport(MembersListFilter $filter): array
    {
        return $this->createFilterQueryBuilder($filter)->getQuery()->getResult();
    }

    private function bindReferentTagsCondition(QueryBuilder $qb, array $referentTags): QueryBuilder
    {
        $tagCondition = 'referentTag IN (:tags)';
        foreach ($referentTags as $referentTag) {
            if ('75' === $referentTag->getCode()) {
                $tagCondition = "(referentTag IN (:tags) OR referentTag.name LIKE '%Paris%')";

                break;
            }
        }

        return $qb
            ->innerJoin('territorial_council.referentTags', 'referentTag')
            ->andWhere($tagCondition)
            ->setParameter('tags', $referentTags)
        ;
    }

    private function createFilterQueryBuilder(MembersListFilter $filter): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('tcm')
            ->addSelect('territorial_council')
            ->innerJoin('tcm.adherent', 'adherent')
            ->innerJoin('tcm.territorialCouncil', 'territorial_council')
            ->leftJoin('tcm.qualities', 'quality')
        ;

        if ($filter->getReferentTags()) {
            $this->bindReferentTagsCondition($qb, $filter->getReferentTags());
        }

        if (false !== \strpos($filter->getSort(), '.')) {
            $sort = $filter->getSort();
        } else {
            $sort = 'tcm.'.$filter->getSort();
        }

        $qb->orderBy($sort, 'd' === $filter->getOrder() ? 'DESC' : 'ASC');

        if ($filter->getTerritorialCouncil()) {
            $qb
                ->andWhere('territorial_council = :territorial_council')
                ->setParameter('territorial_council', $filter->getTerritorialCouncil())
            ;
        }

        if ($lastName = $filter->getLastName()) {
            $qb
                ->andWhere('adherent.lastName LIKE :last_name')
                ->setParameter('last_name', '%'.$lastName.'%')
            ;
        }

        if ($firstName = $filter->getFirstName()) {
            $qb
                ->andWhere('adherent.firstName LIKE :first_name')
                ->setParameter('first_name', '%'.$firstName.'%')
            ;
        }

        if ($gender = $filter->getGender()) {
            switch ($gender) {
                case Genders::FEMALE:
                case Genders::MALE:
                    $qb
                        ->andWhere('adherent.gender = :gender')
                        ->setParameter('gender', $gender)
                    ;

                    break;
                case Genders::UNKNOWN:
                    $qb->andWhere('adherent.gender IS NULL');

                    break;
                default:
                    break;
            }
        }

        if ($ageMin = $filter->getAgeMin()) {
            $now = new \DateTimeImmutable();
            $qb
                ->andWhere('adherent.birthdate <= :min_age_birth_date')
                ->setParameter('min_age_birth_date', $now->sub(new \DateInterval(sprintf('P%dY', $ageMin))))
            ;
        }

        if ($ageMax = $filter->getAgeMax()) {
            $now = new \DateTimeImmutable();
            $qb
                ->andWhere('adherent.birthdate >= :max_age_birth_date')
                ->setParameter('max_age_birth_date', $now->sub(new \DateInterval(sprintf('P%dY', $ageMax))))
            ;
        }

        if ($qualities = $filter->getQualities()) {
            $qb
                ->andWhere('quality.name in (:qualities)')
                ->setParameter('qualities', $qualities)
            ;
        }

        if ($cities = $filter->getCities()) {
            $cities = \array_map(function (Zone $city) {
                return $city->getName();
            }, $cities);
            $qb
                ->andWhere('quality.zone in (:cities)')
                ->setParameter('cities', $cities)
            ;
        }

        if ($committees = $filter->getCommittees()) {
            $committees = \array_map(function (Committee $committee) {
                return $committee->getName();
            }, $committees);
            $qb
                ->andWhere('quality.zone in (:committees)')
                ->setParameter('committees', $committees)
            ;
        }

        if (null !== $filter->getEmailSubscription() && $filter->getSubscriptionType()) {
            $qb
                ->leftJoin('adherent.subscriptionTypes', 'subscriptionType')
                ->addSelect('GROUP_CONCAT(subscriptionType.code) AS HIDDEN st_codes')
                ->groupBy('adherent.id')
            ;

            $subscriptionCondition = 'st_codes LIKE :subscription_code';
            if (false === $filter->getEmailSubscription()) {
                $subscriptionCondition = 'st_codes IS NULL OR st_codes NOT LIKE :subscription_code';
            }

            $qb
                ->having($subscriptionCondition)
                ->setParameter('subscription_code', '%'.$filter->getSubscriptionType().'%')
            ;
        }

        return $qb;
    }

    public function countForTerritorialCouncil(TerritorialCouncil $territorialCouncil, array $qualities = []): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(1)')
            ->where('m.territorialCouncil = :territorial_council')
            ->setParameter('territorial_council', $territorialCouncil)
        ;

        if ($qualities) {
            $qb
                ->innerJoin('m.qualities', 'quality')
                ->andWhere('quality.name IN (:qualities)')
                ->setParameter('qualities', $qualities)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
