<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SkillRepository extends ServiceEntityRepository
{
    public const FIND_FOR_SUMMARY = 'summaries';

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    /**
     * Finds all available skills for autocomplete.
     */
    public function findAvailableSkillsFor(string $term, Adherent $user, string $module): array
    {
        switch ($module) {
            case self::FIND_FOR_SUMMARY:
                $joinedTable = 'summaries';
                $fieldUser = 'member';

                break;
        }

        $qbUserSkills = $this
                ->createQueryBuilder('us')
                ->select('us.slug')
                ->innerJoin(sprintf('us.%s', $joinedTable), 'cv')
                ->andWhere(sprintf('cv.%s = :user', $fieldUser))
            ;

        $qb = $this->createQueryBuilder('s');
        $qb
            ->where('s.slug LIKE :slug')
            ->andWhere($qb->expr()->notIn('s.slug', $qbUserSkills->getDQL()))
            ->setParameters([
                'slug' => $term.'%',
                'user' => $user,
            ])
        ;

        $skills = $qb->getQuery()->getArrayResult();

        foreach ($skills as $skill) {
            $names[] = $skill['name'];
        }

        return $names ?? [];
    }
}
