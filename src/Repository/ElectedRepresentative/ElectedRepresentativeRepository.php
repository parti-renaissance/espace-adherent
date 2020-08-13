<?php

namespace App\Repository\ElectedRepresentative;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\ElectedRepresentative\Filter\ListFilter;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\ElectedRepresentativeTypeEnum;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use App\ValueObject\Genders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ElectedRepresentativeRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ElectedRepresentative::class);
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
        return $this->configurePaginator($this->createFilterQueryBuilder($filter), $page, $limit);
    }

    public function countForReferentTags(array $referentTags): int
    {
        $qb = $this
            ->createQueryBuilder('er')
            ->select('COUNT(DISTINCT er.id)')
        ;
        $this->withActiveMandatesCondition($qb);

        if ($referentTags) {
            $this->withZoneCondition($qb, $referentTags);
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function isInReferentManagedArea(ElectedRepresentative $electedRepresentative, array $referentTags): bool
    {
        $qb = $this->createQueryBuilder('er');

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
        $qb = $this
            ->createQueryBuilder('er')
        ;

        $this->withActiveMandatesCondition($qb);

        if ($filter->getReferentTags()) {
            $this->withZoneCondition($qb, $filter->getReferentTags());
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

        $emailSubscribed = $filter->isEmailSubscribed();
        if (null !== $emailSubscribed) {
            $qb
                ->andWhere('er.emailUnsubscribed = :email_unsubscribed')
                ->setParameter('email_unsubscribed', !$emailSubscribed)
            ;
        }

        return $qb;
    }

    private function withActiveMandatesCondition(QueryBuilder $qb, string $alias = 'er'): QueryBuilder
    {
        return $qb
            ->leftJoin($alias.'.mandates', 'mandate')
            ->leftJoin('mandate.zone', 'zone')
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
        $districtDptCodes = [];
        foreach ($referentTags as $tag) {
            if ($districtDptCode = $tag->getDepartmentCodeFromCirconscriptionName()) {
                $districtDptCodes[] = $districtDptCode;
            }

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

        if ($districtDptCodes) {
            $zoneCondition->add('tag.code IN (:districtDptCodes)');
            $qb->setParameter('districtDptCodes', $districtDptCodes);
        }

        $qb
            ->leftJoin('zone.referentTags', 'tag')
            ->andWhere($zoneCondition)
        ;

        return $qb;
    }
}
