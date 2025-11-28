<?php

declare(strict_types=1);

namespace App\Repository\Jecoute;

use App\Entity\Device;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Entity\Jecoute\Survey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class JemarcheDataSurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JemarcheDataSurvey::class);
    }

    public function countByEmailAnsweredForOneMonth(string $email, \DateTime $postedAt): int
    {
        $endDate = clone $postedAt;

        return $this
            ->createQueryBuilder('jds')
            ->leftJoin('jds.dataSurvey', 'dataSurvey')
            ->select('COUNT(jds.id)')
            ->andWhere('jds.emailAddress = :email')
            ->andWhere('dataSurvey.postedAt >= :startDate')
            ->andWhere('dataSurvey.postedAt < :endDate')
            ->setParameter('email', $email)
            ->setParameter('startDate', $postedAt->modify('-1 month'))
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function iterateForSurvey(Survey $survey, array $zones = []): IterableResult
    {
        $qb = $this->createQueryBuilder('jds')
            ->addSelect('ds', 'adherent')
            ->leftJoin('jds.dataSurvey', 'ds')
            ->leftJoin('ds.author', 'adherent')
            ->where('ds.survey = :survey')
            ->setParameter('survey', $survey)
        ;

        if ($zones) {
            $qb
                ->distinct()
                ->innerJoin('adherent.zones', 'zone')
                ->innerJoin('zone.parents', 'parent')
                ->andWhere('zone IN (:zones) OR parent IN (:zones)')
                ->setParameter('zones', $zones)
            ;
        }

        return $qb->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)->iterate();
    }

    public function countByDevice(Device $device, ?\DateTimeInterface $minPostedAt = null): int
    {
        $qb = $this->createCountByDeviceQueryBuilder($device);

        if ($minPostedAt) {
            $this->applyMinPostedAt($qb, $minPostedAt);
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countByDeviceForLastMonth(Device $device): int
    {
        return $this->countByDevice($device, (new \DateTime('now'))->modify('-1 month'));
    }

    public function createAvailableToContactQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('jds')
            ->andWhere('jds.emailAddress IS NOT NULL AND jds.emailAddress != :empty')
            ->andWhere('jds.agreedToStayInContact = :true')
            ->andWhere('jds.postalCode IS NOT NULL AND jds.postalCode != :empty')
            ->setParameter('true', true)
            ->setParameter('empty', '')
        ;
    }

    public function findLastAvailableToContactByEmail(string $email): ?JemarcheDataSurvey
    {
        return $this->createAvailableToContactQueryBuilder()
            ->leftJoin('jds.dataSurvey', 'dataSurvey')
            ->andWhere('jds.emailAddress = :email')
            ->setParameter('email', $email)
            ->orderBy('dataSurvey.postedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findLastJemarcheDataSurvey(array $zones, int $maxHistory): array
    {
        $qb = $this->createQueryBuilder('jds')
            ->leftJoin('jds.dataSurvey', 'ds')
            ->leftJoin('ds.survey', 'survey')
            ->select('jds.latitude', 'jds.longitude')
            ->addSelect('ds.postedAt AS posted_at', 'survey.name AS survey_name')
            ->where('ds.postedAt >= :last_month')
            ->andWhere('jds.latitude IS NOT NULL OR jds.longitude IS NOT NULL')
            ->setParameter('last_month', new \DateTime("-$maxHistory days"))
        ;

        if ($zones) {
            $qb
                ->distinct()
                ->leftJoin('ds.author', 'author')
                ->innerJoin('author.zones', 'zone')
                ->innerJoin('zone.parents', 'parent')
                ->andWhere('zone IN (:zones) OR parent IN (:zones)')
                ->setParameter('zones', $zones)
            ;
        }

        return $qb
            ->getQuery()
            ->getArrayResult()
        ;
    }

    private function createCountByDeviceQueryBuilder(Device $device): QueryBuilder
    {
        return $this->createQueryBuilder('jds')
            ->select('COUNT(1)')
            ->andWhere('jds.device = :device')
            ->setParameter('device', $device)
        ;
    }

    private function applyMinPostedAt(QueryBuilder $qb, \DateTimeInterface $minPostedAt): void
    {
        if (!\in_array('dataSurvey', $qb->getAllAliases(), true)) {
            $qb->leftJoin(\sprintf('%s.dataSurvey', $qb->getRootAliases()[0]), 'dataSurvey');
        }

        $qb
            ->andWhere('dataSurvey.postedAt >= :min_posted_at')
            ->setParameter('min_posted_at', $minPostedAt)
        ;
    }
}
