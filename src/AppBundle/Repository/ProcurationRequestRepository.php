<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationRequest;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ProcurationRequestRepository extends EntityRepository
{
    public function findAllForExport(): array
    {
        return $this->createQueryBuilder('pr')
            ->select(
                'pr.id AS request_id',
                'pr.gender AS request_birthdate',
                'pr.firstNames AS request_firstNames',
                'pr.lastName AS request_lastName',
                'pr.emailAddress AS request_emailAddress',
                'pr.address AS request_address',
                'pr.postalCode AS request_postalCode',
                'pr.city AS request_city',
                'pr.cityName AS request_cityName',
                'pr.country AS request_country',
                'pr.voteOffice AS request_voteOffice',
                'pr.votePostalCode AS request_votePostalCode',
                'pr.voteCity AS request_voteCity',
                'pr.voteCityName AS request_voteCityName',
                'pr.voteCountry AS request_voteCountry',
                'pr.electionPresidentialFirstRound AS request_electionPresidentialFirstRound',
                'pr.electionPresidentialSecondRound AS request_electionPresidentialSecondRound',
                'pr.electionLegislativeFirstRound AS request_electionLegislativeFirstRound',
                'pr.electionLegislativeSecondRound AS request_electionLegislativeSecondRound',
                'pr.reason AS request_reason',
                'pr.processedAt AS request_processedAt',

                'pp.id AS proposal_id',
                'pp.gender AS proposal_birthdate',
                'pp.firstNames AS proposal_firstNames',
                'pp.lastName AS proposal_lastName',
                'pp.emailAddress AS proposal_emailAddress',
                'pp.address AS proposal_address',
                'pp.postalCode AS proposal_postalCode',
                'pp.city AS proposal_city',
                'pp.cityName AS proposal_cityName',
                'pp.country AS proposal_country',
                'pp.voteOffice AS proposal_voteOffice',
                'pp.votePostalCode AS proposal_votePostalCode',
                'pp.voteCity AS proposal_voteCity',
                'pp.voteCityName AS proposal_voteCityName',
                'pp.voteCountry AS proposal_voteCountry',
                'pp.electionPresidentialFirstRound AS proposal_electionPresidentialFirstRound',
                'pp.electionPresidentialSecondRound AS proposal_electionPresidentialSecondRound',
                'pp.electionLegislativeFirstRound AS proposal_electionLegislativeFirstRound',
                'pp.electionLegislativeSecondRound AS proposal_electionLegislativeSecondRound'
            )
            ->join('pr.foundProxy', 'pp')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @param Adherent $procurationManager
     *
     * @return array
     */
    public function findManagedBy(Adherent $procurationManager)
    {
        if (!$procurationManager->isProcurationManager()) {
            return [];
        }

        $qb = $this->_em->createQueryBuilder();

        $proxiesCountSubRequest = $qb
            ->select('COUNT(pp)')
            ->from('AppBundle:ProcurationProxy', 'pp')
            ->where($qb->expr()->orX(
                $qb->expr()->andX(
                    'pp.voteCountry = \'FR\'',
                    'SUBSTRING(pp.votePostalCode, 1, 2) = SUBSTRING(pr.votePostalCode, 1, 2)',
                    'pp.voteCityName = pr.voteCityName'
                ),
                $qb->expr()->andX(
                    'pp.voteCountry != \'FR\'',
                    'pp.voteCountry = pr.voteCountry'
                )
            ))
            ->andWhere('pp.foundRequest IS NULL')
            ->andWhere('pp.disabled = 0')
            ->andWhere('pp.reliability >= 0')
            ->andWhere($this->createNotMatchingCount().' = 0')
            ->getQuery()
            ->getDQL();

        $qb = $this->createQueryBuilder('pr')
            ->select('pr AS data', '('.$proxiesCountSubRequest.') as matchingProxiesCount')
            ->orderBy('pr.processed', 'ASC')
            ->addOrderBy('pr.createdAt', 'DESC')
            ->addOrderBy('pr.lastName', 'ASC');

        $this->addAndWhereManagedBy($qb, $procurationManager);

        return $qb->getQuery()->getArrayResult();
    }

    public function isManagedBy(Adherent $procurationManager, ProcurationRequest $procurationRequest): bool
    {
        if (!$procurationManager->isProcurationManager()) {
            return false;
        }

        $qb = $this->createQueryBuilder('pr')
            ->select('COUNT(pr)')
            ->where('pr.id = :id')
            ->setParameter('id', $procurationRequest->getId());

        $this->addAndWhereManagedBy($qb, $procurationManager);

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    private function addAndWhereManagedBy(QueryBuilder $qb, Adherent $procurationManager)
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($procurationManager->getProcurationManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        'pr.voteCountry = \'FR\'',
                        $qb->expr()->like('pr.votePostalCode', ':code'.$key)
                    )
                );

                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('pr.voteCountry', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);
    }

    private function createNotMatchingCount()
    {
        $elections = [
            'electionPresidentialFirstRound',
            'electionPresidentialSecondRound',
            'electionLegislativeFirstRound',
            'electionLegislativeSecondRound',
        ];

        $notMatchingCount = [];

        foreach ($elections as $election) {
            $notMatchingCount[] = sprintf('(CASE WHEN (pr.%s = TRUE AND pp.%s = FALSE) THEN 1 ELSE 0 END)', $election, $election);
        }

        return implode(' + ', $notMatchingCount);
    }
}
