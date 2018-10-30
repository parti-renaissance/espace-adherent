<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Entity\ReferentTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SurveyRepository extends ServiceEntityRepository
{
    use ReferentTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Survey::class);
    }

    /**
     * @return Survey[]
     */
    public function findAllFor(Adherent $adherent): array
    {
        return $this
            ->createSurveysForAdherentQueryBuilder($adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    public function createSurveysForAdherentQueryBuilder(Adherent $adherent): QueryBuilder
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect('questions')
            ->innerJoin('survey.questions', 'questions')
            ->innerJoin('survey.creator', 'creator')
            ->innerJoin('creator.managedArea', 'managedArea')
            ->innerJoin('managedArea.tags', 'tags')
            ->andWhere('tags.code IN (:codes)')
            ->setParameter('codes', array_map(function (ReferentTag $tag) {
                return $tag->getCode();
            }, $adherent->getReferentTags()->toArray()))
            ->andWhere('survey.published = true')
        ;
    }

    /**
     * @param Adherent|UserInterface $creator
     *
     * @return Survey[]
     */
    public function findAllByCreator(Adherent $creator): array
    {
        $this->checkReferent($creator);

        return $this
            ->createQueryBuilder('survey')
            ->addSelect('questions')
            ->innerJoin('survey.questions', 'questions')
            ->andWhere('survey.creator = :creator')
            ->setParameter('creator', $creator)
            ->getQuery()
            ->getResult()
        ;
    }
}
