<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityRepository;

class SkillRepository extends EntityRepository
{
    /**
     * Finds all available skills for autocomplete.
     */
    public function findAvailableSkillsForAdherent(string $term, Adherent $user): array
    {
        $qbUserSkills = $this
                ->createQueryBuilder('us')
                ->select('us.slug')
                ->innerJoin('us.summaries', 'cv')
                ->andWhere('cv.member = :user')
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
