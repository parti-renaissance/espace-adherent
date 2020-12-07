<?php

namespace App\Repository\Jecoute;

use App\Entity\Adherent;
use App\Entity\Geo\City;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Repository\ReferentTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

class LocalSurveyRepository extends ServiceEntityRepository
{
    use ReferentTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalSurvey::class);
    }

    /**
     * @return LocalSurvey[]
     */
    public function findAllByAdherent(Adherent $adherent): array
    {
        return $this
            ->createSurveysForAdherentQueryBuilder($adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllByPostalCode(string $postalCode): array
    {
        return $this
            ->createSurveysForPostalCodeQueryBuilder($postalCode)
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
            ->addSelect(sprintf('(SELECT COUNT(q.id) FROM %s AS q WHERE q.survey = survey) AS questions_count', SurveyQuestion::class))
            ->addSelect(sprintf('(SELECT COUNT(r.id) FROM %s AS r WHERE r.survey = survey) AS responses_count', DataSurvey::class))
            ->where('(zone IN (:zones) OR parent IN (:zones) OR child IN (:zones))')
            ->setParameter('zones', $zones)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Adherent|UserInterface $adherent
     */
    public function createSurveysForAdherentQueryBuilder(Adherent $adherent): QueryBuilder
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect('questions', 'zone')
            ->innerJoin('survey.questions', 'questions')
            ->innerJoin('survey.zone', 'zone')
            ->leftJoin('zone.children', 'child')
            ->where('(zone IN (:zones) OR child IN (:zones))')
            ->setParameter('zones', $adherent->getZones())
            ->andWhere('survey.published = true')
        ;
    }

    public function createSurveysForPostalCodeQueryBuilder(string $postalCode): QueryBuilder
    {
        $department = substr($postalCode, 0, 2);

        if ('75' === $department) {
            $qb = $this
                ->createQueryBuilder('survey')
            ;

            return $qb
                ->addSelect('questions', 'zone')
                ->innerJoin('survey.questions', 'questions')
                ->innerJoin('survey.zone', 'zone')
                ->leftJoin('zone.children', 'child')
                ->leftJoin(City::class, 'city', Join::WITH, 'city.code = 75056 AND (zone.code LIKE :paris OR child.code LIKE :paris)')
                ->where(
                    $qb->expr()->orX(
                        'zone.code = :department',
                        'child.code = :department',
                        'city.postalCode LIKE :postal_code_1',
                        'city.postalCode LIKE :postal_code_2'
                    )
                )
                ->andWhere('survey.published = true')
                ->setParameter('postal_code_1', $postalCode.'%')
                ->setParameter('postal_code_2', '%,'.$postalCode.'%')
                ->setParameter('paris', '75%')
                ->setParameter('department', $department)
            ;
        }

        return $this
            ->createQueryBuilder('survey')
            ->addSelect('questions', 'zone')
            ->innerJoin('survey.questions', 'questions')
            ->innerJoin('survey.zone', 'zone')
            ->leftJoin('zone.children', 'child')
            ->leftJoin(City::class, 'city', Join::WITH, 'zone.code = city.code OR child.code = city.code')
            ->where('(zone.code = :department OR child.code = :department OR city.code = :postalCode )')
            ->andWhere('survey.published = true')
            ->setParameter('postalCode', '%'.$postalCode.'%')
            ->setParameter('department', $department)
        ;
    }

    public function findAllByAuthor(Adherent $adherent): array
    {
        return $this
            ->createQueryBuilder('survey')
            ->leftJoin('survey.zone', 'zone')
            ->addSelect('zone')
            ->addSelect(sprintf('(SELECT COUNT(q.id) FROM %s AS q WHERE q.survey = survey) AS questions_count', SurveyQuestion::class))
            ->addSelect(sprintf('(SELECT COUNT(r.id) FROM %s AS r WHERE r.survey = survey) AS responses_count', DataSurvey::class))
            ->where('survey.author = :author')
            ->setParameter('author', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }
}
