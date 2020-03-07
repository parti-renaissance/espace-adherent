<?php

namespace AppBundle\Repository\ReferentOrganizationalChart;

use AppBundle\Entity\Referent;
use AppBundle\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem;
use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferentPersonLinkRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReferentPersonLink::class);
    }

    public function findOrCreateByOrgaItemAndReferent(
        PersonOrganizationalChartItem $organizationalChartItem,
        Referent $referent
    ): ReferentPersonLink {
        return $this->createQueryBuilder('referent_person_link')
            ->where('referent_person_link.referent = :referent')
            ->andWhere('referent_person_link.personOrganizationalChartItem = :personOrganizationalChartItem')
            ->setParameters([
                'referent' => $referent,
                'personOrganizationalChartItem' => $organizationalChartItem,
            ])
            ->getQuery()
            ->getOneOrNullResult() ?? new ReferentPersonLink($organizationalChartItem, $referent)
        ;
    }

    /**
     * @return ReferentPersonLink[]
     */
    public function findByReferentOrdered(Referent $referent): array
    {
        return $this->createQueryOrdered()
            ->where('referent = :referent')
            ->setParameter('referent', $referent)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ReferentPersonLink[]
     */
    public function findByReferentEmail(string $email): array
    {
        return $this->createQueryOrdered()
            ->innerJoin('referent_person_link.referent', 'r')
            ->where('r.emailAddress = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ReferentPersonLink[]
     */
    public function findTeamsOrdered(array $referents): array
    {
        $qb = $this->createQueryOrdered();

        foreach ($referents as $key => $referent) {
            $qb->orWhere("referent = :referent$key")
                ->setParameter("referent$key", $referent)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    private function createQueryOrdered(): QueryBuilder
    {
        return $this->createQueryBuilder('referent_person_link')
            ->addSelect('referent')
            ->addSelect('person_organizational_chart_item')
            ->innerJoin('referent_person_link.personOrganizationalChartItem', 'person_organizational_chart_item')
            ->innerJoin('referent_person_link.referent', 'referent')
            ->innerJoin('referent.areas', 'area')
            ->orderBy('area.areaCode', 'ASC')
            ->addOrderBy('referent_person_link.referent', 'ASC')
            ->addOrderBy('person_organizational_chart_item.root', 'ASC')
            ->addOrderBy('person_organizational_chart_item.parent', 'ASC')
            ->addOrderBy('person_organizational_chart_item.lvl', 'ASC')
        ;
    }
}
