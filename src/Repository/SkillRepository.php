<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityRepository;

class SkillRepository extends EntityRepository
{
    const FIND_FOR_SUMMARY = 'summaries';
    const FIND_FOR_CITIZEN_INITIATIVE = 'citizenInitiatives';

    /**
     * Finds all available skills for autocomplete.
     */
    public function findAvailableSkillsFor(string $term, Adherent $user, string $module): array
    {
        switch ($module) {
            case self::FIND_FOR_SUMMARY:
                $joinedTable = 'summaries';
                $fieldUser = 'member';
            case self::FIND_FOR_CITIZEN_INITIATIVE:
                $joinedTable = 'citizenInitiatives';
                $fieldUser = 'organizer';
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
