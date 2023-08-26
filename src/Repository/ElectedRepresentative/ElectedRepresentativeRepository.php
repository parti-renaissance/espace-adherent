<?php

namespace App\Repository\ElectedRepresentative;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\ElectedRepresentative\Filter\ListFilter;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\ElectedRepresentativeTypeEnum;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\Geo\Zone;
use App\Repository\GeoZoneTrait;
use App\Repository\Helper\MembershipFilterHelper;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ElectedRepresentativeRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectedRepresentative::class);
    }

    public function findOneByAdherentUuid(string $uuid): ?ElectedRepresentative
    {
        return $this
            ->createQueryBuilder('elected_representative')
            ->innerJoin('elected_representative.adherent', 'adherent')
            ->andWhere('adherent.uuid = :adherent_uuid')
            ->setParameter('adherent_uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByEmail(string $email): array
    {
        return $this
            ->createQueryBuilder('elected_representative')
            ->leftJoin('elected_representative.adherent', 'adherent')
            ->andWhere((new Orx())
                ->add('adherent.emailAddress = :email')
                ->add('elected_representative.contactEmail = :email')
            )
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult()
        ;
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
                $query->enableResultCache(1800);
            }
        );
    }

    /**
     * @param Zone[] $zones
     */
    public function countForZones(array $zones): int
    {
        $qb = $this
            ->createQueryBuilder('er')
            ->select('COUNT(DISTINCT er.id)')
        ;
        $this->withActiveMandatesCondition($qb);

        if ($zones) {
            $this->withZoneCondition($qb, $zones);
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function isInReferentManagedArea(ElectedRepresentative $electedRepresentative, array $zones): bool
    {
        $qb = $this->createQueryBuilder('er');

        $this->withActiveMandatesCondition($qb);

        $res = $this
            ->withZoneCondition($qb, $zones)
            ->andWhere('er = :electedRepresentative')
            ->setParameter('electedRepresentative', $electedRepresentative)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return null !== $res;
    }

    public function createWithEmailQueryBuilder(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('elected_representative')
            ->andWhere((new Orx())
                ->add('elected_representative.adherent IS NOT NULL')
                ->add('elected_representative.contactEmail IS NOT NULL')
            )
        ;
    }

    private function createFilterQueryBuilder(ListFilter $filter): QueryBuilder
    {
        $qb = $this->createQueryBuilder('er');

        $this->withActiveMandatesCondition($qb);
        $qb->andWhere('mandate IS NOT NULL');

        $authorCondition = new Orx();

        if ($filter->createdOrUpdatedByAdherent) {
            $authorCondition->add('er.createdByAdherent = :created_or_updated_by_adherent OR er.updatedByAdherent = :created_or_updated_by_adherent');
            $qb->setParameter('created_or_updated_by_adherent', $filter->createdOrUpdatedByAdherent);
        }

        if ($zones = $filter->getZones() ?: $filter->getManagedZones()) {
            $this->withZoneCondition($qb, $zones, 'er', $authorCondition);
        }

        if ($filter->getManagedZones()) {
            $qb
                ->orderBy('er.'.$filter->getSort(), 'd' === $filter->getOrder() ? 'DESC' : 'ASC')
                ->addOrderBy('mandate.number', 'ASC')
            ;
        }

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
            switch ($gender) {
                case Genders::FEMALE:
                case Genders::MALE:
                    $qb
                        ->andWhere('er.gender = :gender')
                        ->setParameter('gender', $gender)
                    ;

                    break;
                case Genders::UNKNOWN:
                    $qb->andWhere('er.gender IS NULL');

                    break;
                default:
                    break;
            }
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

        if ($mandateType = $filter->getMandateType()) {
            switch ($mandateType) {
                case MandateTypeEnum::TYPE_NATIONAL:
                    $qb->andWhere('mandate.type IN (:national_mandates)');
                    $qb->setParameter('national_mandates', MandateTypeEnum::NATIONAL_MANDATES);

                    break;
                case MandateTypeEnum::TYPE_LOCAL:
                    $qb->andWhere('mandate.type IN (:local_mandates)');
                    $qb->setParameter('local_mandates', MandateTypeEnum::LOCAL_MANDATES);

                    break;
            }
        }

        if ($politicalFunctions = $filter->getPoliticalFunctions()) {
            $qb
                ->andWhere('politicalFunction.name in (:politicalFunctions)')
                ->andWhere('politicalFunction.onGoing = 1')
                ->andWhere('politicalFunction.finishAt IS NULL')
                ->setParameter('politicalFunctions', $politicalFunctions)
            ;
        }

        if ($userListDefinitions = $filter->getUserListDefinitions()) {
            $qb
                ->andWhere('userListDefinition.id in (:userListDefinitions)')
                ->setParameter('userListDefinitions', $userListDefinitions)
            ;
        }

        if ($contactType = $filter->getContactType()) {
            switch ($contactType) {
                case ElectedRepresentativeTypeEnum::ADHERENT:
                    $qb->andWhere('er.adherent IS NOT NULL');

                    break;
                case ElectedRepresentativeTypeEnum::CONTACT:
                    $qb
                        ->andWhere('er.adherent IS NULL')
                        ->andWhere('er.contactEmail IS NOT NULL')
                    ;

                    break;
                case ElectedRepresentativeTypeEnum::OTHER:
                    $qb
                        ->andWhere('er.adherent IS NULL')
                        ->andWhere('er.contactEmail IS NULL')
                    ;

                    break;
                default:
                    throw new \InvalidArgumentException("ElectedRepresentative contactType \"$contactType\" is undefined.");
            }
        }

        $emailSubscription = $filter->isEmailSubscription();
        if (null !== $emailSubscription) {
            $qb
                ->andWhere('er.emailUnsubscribed = :email_unsubscribed')
                ->setParameter('email_unsubscribed', !$emailSubscription)
            ;
        }

        $revenueDeclared = $filter->isRevenueDeclared();
        if (null !== $revenueDeclared) {
            $qb->andWhere(sprintf('er.contributionStatus %s NULL', $revenueDeclared ? 'IS NOT' : 'IS'));
        }

        $contributionActive = $filter->isContributionActive();
        if (null !== $contributionActive) {
            $qb->andWhere(sprintf('er.lastContribution %s NULL', $contributionActive ? 'IS NOT' : 'IS'));
        }

        if ($renaissanceMembership = $filter->getRenaissanceMembership()) {
            $this->withRenaissanceMembership($qb, $renaissanceMembership);
        }

        if ($committees = $filter->getCommitteeUuids()) {
            if (!\in_array('adherent', $qb->getAllAliases(), true)) {
                $qb->innerJoin('er.adherent', 'adherent');
            }

            $qb
                ->innerJoin('adherent.memberships', 'membership')
                ->innerJoin('membership.committee', 'committee')
                ->andWhere('committee.uuid IN (:committees)')
                ->setParameter('committees', $committees)
            ;
        }

        return $qb;
    }

    private function withActiveMandatesCondition(QueryBuilder $qb, string $alias = 'er'): QueryBuilder
    {
        return $qb
            ->leftJoin($alias.'.mandates', 'mandate', Join::WITH, '(mandate.finishAt IS NULL OR mandate.finishAt > :now) AND mandate.onGoing = 1 AND mandate.isElected = 1')
            ->leftJoin('mandate.geoZone', 'zone')
            ->setParameter('now', new \DateTime())
        ;
    }

    private function withZoneCondition(
        QueryBuilder $qb,
        array $zones,
        string $alias = 'er',
        Composite $condition = null
    ): QueryBuilder {
        if (!$zones) {
            return $qb;
        }

        if (!\in_array('mandate', $qb->getAllAliases(), true)) {
            $qb->leftJoin($alias.'.mandates', 'mandate');
        }

        $zoneConditionQueryBuilder = $this->createGeoZonesQueryBuilder(
            $zones,
            $qb,
            Mandate::class,
            'mandate_2',
            'geoZone',
            'mandate_zone_2'
        );

        $qb->andWhere(
            ($condition ?? new Orx())
                ->add(sprintf('mandate.id IN (%s)', $zoneConditionQueryBuilder->getDQL()))
        );

        return $qb;
    }

    public function hasActiveParliamentaryMandate(Adherent $adherent): bool
    {
        return 0 < (int) $this->createQueryBuilder('e')
            ->select('COUNT(1)')
            ->innerJoin('e.mandates', 'm')
            ->where('m.onGoing = :true AND m.isElected = :true AND m.finishAt IS NULL')
            ->andWhere('m.type IN (:types)')
            ->andWhere('e.adherent = :adherent')
            ->setParameters([
                'true' => true,
                'adherent' => $adherent,
                'types' => [
                    MandateTypeEnum::SENATOR,
                    MandateTypeEnum::DEPUTY,
                    MandateTypeEnum::EURO_DEPUTY,
                ],
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getAdherentMandateTypes(Adherent $adherent): array
    {
        $mandateTypes = $this->createQueryBuilder('elected_representative')
            ->select('DISTINCT(mandate.type)')
            ->innerJoin(
                'elected_representative.mandates',
                'mandate',
                Join::WITH,
                '(mandate.finishAt IS NULL OR mandate.finishAt > :now) AND mandate.onGoing = 1 AND mandate.isElected = 1'
            )
            ->andWhere('elected_representative.adherent = :adherent')
            ->setParameters([
                'adherent' => $adherent,
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map('current', $mandateTypes);
    }

    private function withRenaissanceMembership(
        QueryBuilder $qb,
        string $renaissanceMembership,
        string $alias = 'er',
        string $adherentAlias = 'adherent'
    ): QueryBuilder {
        if (!\in_array('adherent', $qb->getAllAliases(), true)) {
            $qb->innerJoin($alias.'.adherent', $adherentAlias);
        }
        MembershipFilterHelper::withMembershipFilter($qb, $adherentAlias, $renaissanceMembership);

        return $qb;
    }
}
