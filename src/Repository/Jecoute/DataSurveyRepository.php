<?php

namespace App\Repository\Jecoute;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Phoning\Campaign;
use App\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query;
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
    public function findPhoningCampaignDataSurveys(Campaign $campaign, int $page = 1, ?int $limit = 30): iterable
    {
        $qb = $this
            ->createQueryBuilder('ds')
            ->leftJoin('ds.survey', 'survey')
            ->leftJoin('survey.questions', 'surveyQuestion')
            ->leftJoin('surveyQuestion.question', 'question')
            ->leftJoin('surveyQuestion.dataAnswers', 'dataAnswer')
            ->leftJoin('dataAnswer.selectedChoices', 'selectedChoice')
            ->leftJoin('ds.campaignHistory', 'campaignHistory')
            ->leftJoin('campaignHistory.campaign', 'campaign')
            ->addSelect('survey', 'surveyQuestion', 'question', 'dataAnswer', 'selectedChoice', 'campaignHistory', 'campaign')
            ->where('campaign = :campaign')
            ->setParameter('campaign', $campaign)
        ;

        if (!$limit) {
            return $qb->getQuery()->getResult();
        }

        return $this->configurePaginator($qb, $page, $limit);
    }

    public function iterateForPhoningCampaignDataSurveys(Campaign $campaign): IterableResult
    {
        return $this->createQueryBuilder('ds')
            ->addSelect('survey', 'campaignHistory', 'author', 'adherent', 'campaign')
            ->leftJoin('ds.survey', 'survey')
            ->leftJoin('ds.author', 'author')
            ->leftJoin('ds.campaignHistory', 'campaignHistory')
            ->leftJoin('campaignHistory.campaign', 'campaign')
            ->leftJoin('campaignHistory.adherent', 'adherent')
            ->where('campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->iterate()
        ;
    }

    public function iterateForSurvey(Survey $survey, array $zones = []): IterableResult
    {
        $qb = $this->createQueryBuilder('ds')
            ->addSelect('jemarcheDataSurvey')
            ->addSelect('partial campaignHistory.{id}')
            ->addSelect('partial author.{id, firstName, lastName}')
            ->addSelect('partial adherent.{id, firstName, lastName, emailAddress, postAddress.postalCode, gender, position}')
            ->leftJoin('ds.author', 'author')
            ->leftJoin('ds.jemarcheDataSurvey', 'jemarcheDataSurvey')
            ->leftJoin('ds.campaignHistory', 'campaignHistory')
            ->leftJoin('campaignHistory.adherent', 'adherent')
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

        return $qb->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)->iterate();
    }
}
