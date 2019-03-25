<?php

namespace AppBundle\Repository\Jecoute;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Repository\ReferentTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
            ->setParameter('codes', $adherent->getReferentTagsCodes())
            ->andWhere('survey.published = true')
        ;
    }

    /**
     * @return LocalSurvey[]
     */
    public function findAllByAuthor(Adherent $author): array
    {
        $this->checkReferent($author);

        return $this
            ->createQueryBuilder('survey')
            ->addSelect('questions')
            ->innerJoin('survey.questions', 'questions')
            ->andWhere('survey.author = :author')
            ->andWhere('survey INSTANCE OF '.LocalSurvey::class)
            ->setParameter('author', $author)
            ->getQuery()
            ->getResult()
        ;
    }
}
