<?php

namespace AppBundle\Repository\Jecoute;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Entity\ReferentTag;
use AppBundle\Repository\ReferentTrait;
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
    public function findAllByAdherent(UserInterface $adherent): array
    {
        return $this
            ->createSurveysForAdherentQueryBuilder($adherent)
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
            ->addSelect('questions')
            ->innerJoin('survey.questions', 'questions')
            ->innerJoin('survey.author', 'author')
            ->innerJoin('author.managedArea', 'managedArea')
            ->innerJoin('managedArea.tags', 'tags')
            ->andWhere('tags.code IN (:codes)')
            ->setParameter('codes', array_map(function (ReferentTag $tag) {
                return $tag->getCode();
            }, $adherent->getReferentTags()->toArray()))
            ->andWhere('survey.published = true')
        ;
    }

    /**
     * @return Survey[]
     */
    public function findAllByAuthor(Adherent $author): array
    {
        $this->checkReferent($author);

        return $this
            ->createQueryBuilder('survey')
            ->addSelect('questions')
            ->innerJoin('survey.questions', 'questions')
            ->andWhere('survey.author = :author')
            ->setParameter('author', $author)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByUuid(string $uuid): ?Survey
    {
        return $this
            ->createQueryBuilder('survey')
            ->addSelect('surveyQuestion', 'question', 'choices')
            ->innerJoin('survey.questions', 'surveyQuestion')
            ->innerJoin('surveyQuestion.question', 'question')
            ->leftJoin('question.choices', 'choices')
            ->innerJoin('survey.author', 'author')
            ->andWhere('survey.uuid = :uuid')
            ->andWhere('survey.published = true')
            ->setParameter('uuid', $uuid)
            ->addOrderBy('surveyQuestion.position', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
