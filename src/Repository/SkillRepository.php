<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityRepository;

class SkillRepository extends EntityRepository
{
    /**
     * Finds all available skills for autocomplete that connected user has not yet chosen.
     */
    public function findAvailableSkillsForAdherent(string $term, Adherent $user): array
    {
        $qbUserSkills = $this
            ->createQueryBuilder('us')
            ->select('us.slug')
            ->leftJoin('us.summary', 'cv')
            ->andWhere('cv.member = :user')
        ;

        $qb = $this->createQueryBuilder('s');
        $qb
            ->select('s.slug, s.name')
            ->distinct()
            ->leftJoin('s.summary', 'summary')
            ->where('s.slug LIKE :slug')
            ->andWhere('summary.member != :member')
            ->andWhere($qb->expr()->notIn('s.slug', $qbUserSkills->getDQL()))
            ->setParameters([
                'slug' => $term.'%',
                'member' => $user,
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
