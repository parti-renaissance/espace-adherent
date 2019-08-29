<?php

namespace AppBundle\Repository\Jecoute;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Repository\ReferentTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LocalSurveyRepository extends ServiceEntityRepository
{
    use ReferentTrait;

    public function __construct(RegistryInterface $registry)
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

    public function findAllByTags(array $tags): array
    {
        $qb = $this
            ->createQueryBuilder('survey')
            ->addSelect('questions')
            ->innerJoin('survey.questions', 'questions')
        ;

        return $qb
            ->andWhere($this->createOrExpressionForSurveyTags($qb, $tags))
            ->orderBy('survey.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Adherent|UserInterface $adherent
     */
    public function createSurveysForAdherentQueryBuilder(Adherent $adherent): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('survey')
            ->addSelect('questions')
            ->innerJoin('survey.questions', 'questions')
        ;

        return $qb
            ->where($this->createOrExpressionForSurveyTags($qb, $adherent->getReferentTagCodes()))
            ->andWhere('survey.published = true')
        ;
    }

    public function createOrExpressionForSurveyTags(QueryBuilder $qb, array $tags): Orx
    {
        $expression = new Orx();

        foreach ($tags as $key => $tag) {
            $expression->add(":tags_$key = ANY_OF(string_to_array(survey.tags, ','))");
            $qb->setParameter("tags_$key", $tag);
        }

        return $expression;
    }
}
