<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CitizenProjectCommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CitizenProjectComment::class);
    }

    /**
     * @return CitizenProjectComment[]
     */
    public function findForProject(CitizenProject $citizenProject): array
    {
        return $this->findBy(['citizenProject' => $citizenProject], ['createdAt' => 'DESC']);
    }

    /**
     * @return CitizenProjectComment[]
     */
    public function findForAuthor(Adherent $author): array
    {
        return $this->findBy(['author' => $author]);
    }

    public function removeForAuthor(Adherent $author): void
    {
        $qb = $this->createQueryBuilder('comment');

        $qb
            ->update()
            ->set('comment.author', 'null')
            ->where('comment.author = :author')
            ->setParameter(':author', $author)
        ;

        $qb->getQuery()->execute();
    }
}
