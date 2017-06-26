<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityRepository;

class SkillRepository extends EntityRepository
{
    /**
     * Finds all availeble skills for autocomplete that connected user has not yet chosen.
     */
    public function findAvailableSkillsForAdherent(string $term, Adherent $user): array
    {
        $qbUserSkills = $this
            ->createQueryBuilder('us')
            ->select('us.name')
            ->leftJoin('us.summary', 'cv')
            ->andWhere('cv.member = :user')
        ;

        $qb = $this->createQueryBuilder('s');
        $qb
            ->distinct('s.name')
            ->leftJoin('s.summary', 'summary')
            ->where('s.name LIKE :name')
            ->andWhere('summary.member != :member')
            ->andWhere($qb->expr()->notIn('s.name', $qbUserSkills->getDQL()))
            ->setParameters([
                'name' => $term.'%',
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
