<?php

declare(strict_types=1);

namespace App\Repository\Jecoute;

use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\SurveyQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Jecoute\LocalSurvey>
 */
class LocalSurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalSurvey::class);
    }

    public function countForZones(array $zones, bool $publishedOnly = false): int
    {
        $qb = $this
            ->createQueryBuilder('survey')
            ->select('COUNT(DISTINCT(survey.id))')
        ;

        if (!empty($zones)) {
            $qb
                ->innerJoin('survey.zone', 'zone')
                ->leftJoin('zone.children', 'child')
                ->where('(zone IN (:zones) OR child IN (:zones))')
                ->setParameter('zones', $zones)
            ;
        }

        if ($publishedOnly) {
            $qb->andWhere('survey.published = TRUE');
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return LocalSurvey[]
     */
    public function findAllByZones(array $zones): array
    {
        return $this
            ->createSurveysByZonesQueryBuilder($zones)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return LocalSurvey[]
     */
    public function findAllByZonesWithStats(array $zones): array
    {
        return $this
            ->createQueryBuilder('survey')
            ->leftJoin('survey.zone', 'zone')
            ->leftJoin('zone.parents', 'parent')
            ->leftJoin('zone.children', 'child')
            ->addSelect('zone')
            ->addSelect(\sprintf('(SELECT COUNT(q.id) FROM %s AS q WHERE q.survey = survey) AS questions_count', SurveyQuestion::class))
            ->addSelect(\sprintf('(SELECT COUNT(r.id) FROM %s AS r WHERE r.survey = survey) AS responses_count', DataSurvey::class))
            ->where('(zone IN (:zones) OR parent IN (:zones) OR child IN (:zones))')
            ->setParameter('zones', $zones)
            ->orderBy('survey.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function createSurveysByZonesQueryBuilder(array $zones): QueryBuilder
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect('survey_question', 'question', 'zone', 'choice')
            ->innerJoin('survey.questions', 'survey_question')
            ->innerJoin('survey_question.question', 'question')
            ->leftJoin('question.choices', 'choice')
            ->innerJoin('survey.zone', 'zone')
            ->leftJoin('zone.children', 'child')
            ->where('(zone IN (:zones) OR child IN (:zones))')
            ->setParameter('zones', $zones)
            ->andWhere('survey.published = true')
        ;
    }
}
