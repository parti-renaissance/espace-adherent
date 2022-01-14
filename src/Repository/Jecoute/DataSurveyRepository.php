<?php

namespace App\Repository\Jecoute;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Pap\Building;
use App\Entity\Pap\Campaign as PapCampaign;
use App\Entity\Pap\CampaignHistory;
use App\Entity\Phoning\Campaign as PhoningCampaign;
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
            ->leftJoin('surveyQuestion.dataAnswers', 'dataAnswer')
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
    public function findPapCampaignDataSurveys(PapCampaign $campaign, int $page = 1, ?int $limit = 30): iterable
    {
        $qb = $this
            ->createQueryBuilder('ds')
            ->leftJoin('ds.survey', 'survey')
            ->leftJoin('survey.questions', 'surveyQuestion')
            ->leftJoin('surveyQuestion.question', 'question')
            ->leftJoin('surveyQuestion.dataAnswers', 'dataAnswer')
            ->leftJoin('dataAnswer.selectedChoices', 'selectedChoice')
            ->leftJoin('ds.papCampaignHistory', 'campaignHistory')
            ->leftJoin('campaignHistory.campaign', 'campaign')
            ->addSelect('survey', 'surveyQuestion', 'question', 'dataAnswer', 'selectedChoice', 'campaignHistory', 'campaign')
            ->where('campaign = :campaign')
            ->orderBy('campaignHistory.createdAt', 'DESC')
            ->setParameter('campaign', $campaign)
        ;

        if (!$limit) {
            return $qb->getQuery()->getResult();
        }

        return $this->configurePaginator($qb, $page, $limit);
    }

    public function iterateForPapCampaignDataSurveys(PapCampaign $campaign): IterableResult
    {
        return $this->createQueryBuilder('ds')
            ->addSelect('survey', 'campaignHistory', 'author', 'campaign')
            ->leftJoin('ds.survey', 'survey')
            ->leftJoin('ds.author', 'author')
            ->leftJoin('ds.papCampaignHistory', 'campaignHistory')
            ->leftJoin('campaignHistory.campaign', 'campaign')
            ->where('campaign = :campaign')
            ->orderBy('campaignHistory.createdAt', 'DESC')
            ->setParameter('campaign', $campaign)
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
        ?int $limit = 30
    ): iterable {
        $queryBuilder = $this->createSurveyQueryBuilder($survey, $zones, $departmentCodes);

        if (!$limit) {
            return $queryBuilder->getQuery()->getResult();
        }

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }

    public function countSurveysForBuilding(Building $building, string $buildingBlock = null, int $floor = null): int
    {
        $conditions = '';
        $params = [
            'building' => $building,
        ];
        if ($buildingBlock) {
            $conditions = ' AND campaignHistory.buildingBlock = :buildingBlock';
            $params += ['buildingBlock' => $buildingBlock];
        }

        if ($floor) {
            $conditions .= ' AND campaignHistory.floor = :floor';
            $params += ['floor' => $floor];
        }

        return (int) $this
            ->createQueryBuilder('ds')
            ->select('COUNT(1)')
            ->leftJoin(CampaignHistory::class, 'campaignHistory', Join::WITH, 'campaignHistory.dataSurvey = ds')
            ->where(sprintf('campaignHistory.building = :building %s', $conditions))
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

    private function createSurveyQueryBuilder(
        Survey $survey,
        array $zones = [],
        array $departmentCodes = []
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('ds')
            ->addSelect('jemarcheDataSurvey')
            ->addSelect('partial phoningCampaignHistory.{id, uuid, beginAt, finishAt}')
            ->addSelect('partial papCampaignHistory.{id, uuid, beginAt, finishAt, firstName, lastName, emailAddress, gender, ageRange}')
            ->addSelect('partial author.{id, firstName, lastName, gender, birthdate, uuid}')
            ->addSelect('partial adherent.{id, firstName, lastName, emailAddress, postAddress.postalCode, gender, position, birthdate}')
            ->leftJoin('ds.author', 'author')
            ->leftJoin('ds.jemarcheDataSurvey', 'jemarcheDataSurvey')
            ->leftJoin('ds.phoningCampaignHistory', 'phoningCampaignHistory')
            ->leftJoin('ds.papCampaignHistory', 'papCampaignHistory')
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
