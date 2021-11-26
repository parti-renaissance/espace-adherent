<?php

namespace App\Repository\Jecoute;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Jecoute\DataSurvey;
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
}
