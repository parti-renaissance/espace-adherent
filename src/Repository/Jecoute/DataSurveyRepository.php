<?php

declare(strict_types=1);

namespace App\Repository\Jecoute;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Adherent;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Pap\Address;
use App\Entity\Pap\Building;
use App\Entity\Pap\Campaign as PapCampaign;
use App\Entity\Pap\CampaignHistory;
use App\Entity\Pap\VotePlace;
use App\Entity\Phoning\Campaign as PhoningCampaign;
use App\Repository\GeoZoneTrait;
use App\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class DataSurveyRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataSurvey::class);
    }

    /**
     * @return DataSurvey[]|PaginatorInterface|iterable
     */
    public function findPhoningCampaignDataSurveys(PhoningCampaign $campaign, int $page = 1, ?int $limit = 30): iterable
    {
        $qb = $this
            ->createQueryBuilder('ds')
            ->leftJoin('ds.survey', 'survey')
            ->leftJoin('survey.questions', 'surveyQuestion')
            ->leftJoin('surveyQuestion.question', 'question')
            ->leftJoin('surveyQuestion.dataAnswers', 'dataAnswer', Join::WITH, 'dataAnswer.dataSurvey = ds')
            ->leftJoin('dataAnswer.selectedChoices', 'selectedChoice')
            ->leftJoin('ds.phoningCampaignHistory', 'campaignHistory')
            ->leftJoin('campaignHistory.campaign', 'campaign')
            ->addSelect('survey', 'surveyQuestion', 'question', 'dataAnswer', 'selectedChoice', 'campaignHistory', 'campaign')
            ->where('campaign = :campaign')
            ->orderBy('campaignHistory.beginAt', 'DESC')
            ->setParameter('campaign', $campaign)
        ;

        if (!$limit) {
            return $qb->getQuery()->getResult();
        }

        return $this->configurePaginator($qb, $page, $limit);
    }

    public function iterateForPhoningCampaignDataSurveys(PhoningCampaign $campaign): IterableResult
    {
        return $this->createQueryBuilder('ds')
            ->addSelect('survey', 'campaignHistory', 'author', 'adherent', 'campaign')
            ->leftJoin('ds.survey', 'survey')
            ->leftJoin('ds.author', 'author')
            ->leftJoin('ds.phoningCampaignHistory', 'campaignHistory')
            ->leftJoin('campaignHistory.campaign', 'campaign')
            ->leftJoin('campaignHistory.adherent', 'adherent')
            ->where('campaign = :campaign')
            ->orderBy('campaignHistory.beginAt', 'DESC')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->iterate()
        ;
    }

    /**
     * @return DataSurvey[]|PaginatorInterface|iterable
     */
    public function findPapCampaignDataSurveys(
        PapCampaign $campaign,
        array $zones,
        int $page = 1,
        ?int $limit = 30,
    ): iterable {
        $qb = $this
            ->createQueryBuilder('data_survey')
            ->select(
                'campaign_history',
                'data_survey',
                'survey',
            )
            ->innerJoin('data_survey.survey', 'survey')
            ->innerJoin('data_survey.papCampaignHistory', 'campaign_history')
            ->where('campaign_history.campaign = :campaign')
            ->orderBy('campaign_history.createdAt', 'DESC')
            ->setParameter('campaign', $campaign)
        ;

        if ($zones) {
            $qb
                ->innerJoin('campaign_history.building', 'building')
                ->innerJoin('building.address', 'address')
                ->innerJoin('address.votePlace', 'vote_place')
            ;
            $this->withGeoZones(
                $zones,
                $qb,
                'vote_place',
                VotePlace::class,
                'vp2',
                'zone',
                'z2'
            );
        }

        if (!$limit) {
            return $qb->getQuery()->getResult();
        }

        return $this->configurePaginator($qb, $page, $limit);
    }

    public function iterateForPapCampaignDataSurveys(PapCampaign $campaign, array $zones = []): IterableResult
    {
        $qb = $this->createQueryBuilder('ds')
            ->addSelect('survey', 'campaignHistory', 'author', 'campaign')
            ->addSelect('partial building.{id}')
            ->addSelect('partial address.{id, postalCodes, longitude, latitude}')
            ->leftJoin('ds.survey', 'survey')
            ->leftJoin('ds.author', 'author')
            ->leftJoin('ds.papCampaignHistory', 'campaignHistory')
            ->leftJoin('campaignHistory.building', 'building')
            ->leftJoin('building.address', 'address')
            ->leftJoin('campaignHistory.campaign', 'campaign')
            ->where('campaign = :campaign')
            ->setParameter('campaign', $campaign)
        ;

        if ($zones) {
            $addressIds = array_column($this->createEntityInGeoZonesQueryBuilder(
                $zones,
                Address::class,
                'a2',
                'zones',
                'z2'
            )
                ->getQuery()
                ->getArrayResult(), 'id'
            );

            $qb
                ->andWhere('address.id IN (:ids)')
                ->setParameter('ids', $addressIds)
            ;
        }

        return $qb
            ->orderBy('campaignHistory.createdAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->iterate()
        ;
    }

    public function iterateForSurvey(Survey $survey, array $zones = [], array $departmentCodes = []): IterableResult
    {
        return $this->createSurveyQueryBuilder($survey, $zones, $departmentCodes)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->iterate()
        ;
    }

    /**
     * @return DataSurvey[]|PaginatorInterface|iterable
     */
    public function findDataSurveyForSurvey(
        Survey $survey,
        array $zones = [],
        array $departmentCodes = [],
        int $page = 1,
        ?int $limit = 30,
    ): iterable {
        $queryBuilder = $this->createSurveyQueryBuilder($survey, $zones, $departmentCodes);

        if (!$limit) {
            return $queryBuilder->getQuery()->getResult();
        }

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }

    public function countSurveysForBuilding(Building $building, ?string $buildingBlock = null, ?int $floor = null): int
    {
        $conditions = '';
        $params = [
            'building' => $building,
        ];
        if ($buildingBlock) {
            $conditions = ' AND campaignHistory.buildingBlock = :buildingBlock';
            $params += ['buildingBlock' => $buildingBlock];
        }

        if (null !== $floor) {
            $conditions .= ' AND campaignHistory.floor = :floor';
            $params += ['floor' => $floor];
        }

        return (int) $this
            ->createQueryBuilder('ds')
            ->select('COUNT(1)')
            ->leftJoin(CampaignHistory::class, 'campaignHistory', Join::WITH, 'campaignHistory.dataSurvey = ds')
            ->where(\sprintf('campaignHistory.building = :building %s', $conditions))
            ->setParameters($params)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countSurveyDataAnswer(Survey $survey): int
    {
        return (int) $this
            ->createQueryBuilder('ds')
            ->select('COUNT(1)')
            ->where('ds.survey = :survey')
            ->setParameter('survey', $survey)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countByAdherent(Adherent $adherent, ?\DateTimeInterface $minPostedAt = null): int
    {
        $qb = $this->createQueryBuilder('dataSurvey')
            ->select('COUNT(1)')
            ->andWhere('dataSurvey.author = :adherent')
            ->setParameter('adherent', $adherent)
        ;

        if ($minPostedAt) {
            $qb
                ->andWhere('dataSurvey.postedAt >= :min_posted_at')
                ->setParameter('min_posted_at', $minPostedAt)
            ;
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countByAdherentForLastMonth(Adherent $adherent): int
    {
        return $this->countByAdherent($adherent, new \DateTime('now')->modify('-1 month'));
    }

    private function createSurveyQueryBuilder(
        Survey $survey,
        array $zones = [],
        array $departmentCodes = [],
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('ds')
            ->addSelect('jemarcheDataSurvey')
            ->addSelect('partial phoningCampaignHistory.{id, uuid, beginAt, finishAt}')
            ->addSelect('partial papCampaignHistory.{id, uuid, beginAt, finishAt, firstName, lastName, emailAddress, gender, ageRange}')
            ->addSelect('partial building.{id}')
            ->addSelect('partial address.{id, postalCodes, longitude, latitude}')
            ->addSelect('partial author.{id, firstName, lastName, gender, birthdate, uuid, postAddress.postalCode}')
            ->addSelect('partial adherent.{id, firstName, lastName, emailAddress, postAddress.postalCode, gender, position, birthdate}')
            ->leftJoin('ds.author', 'author')
            ->leftJoin('ds.jemarcheDataSurvey', 'jemarcheDataSurvey')
            ->leftJoin('ds.phoningCampaignHistory', 'phoningCampaignHistory')
            ->leftJoin('ds.papCampaignHistory', 'papCampaignHistory')
            ->leftJoin('papCampaignHistory.building', 'building')
            ->leftJoin('building.address', 'address')
            ->leftJoin('phoningCampaignHistory.adherent', 'adherent')
            ->where('ds.survey = :survey')
            ->setParameter('survey', $survey)
        ;

        if ($zones) {
            $qb
                ->distinct()
                ->innerJoin('author.zones', 'zone')
                ->innerJoin('zone.parents', 'parent')
                ->andWhere('zone IN (:zones) OR parent IN (:zones)')
                ->setParameter('zones', $zones)
            ;
        }

        if ($departmentCodes) {
            $postalCodeExpression = $qb->expr()->orX();
            foreach ($departmentCodes as $key => $code) {
                $postalCodeExpression->add("ds.authorPostalCode LIKE :code_$key");
                $qb->setParameter("code_$key", "$code%");
            }
            $qb->andWhere($postalCodeExpression);
        }

        return $qb;
    }
}
