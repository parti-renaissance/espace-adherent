<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SkillRepository extends EntityRepository
{
    /**
     * Finds all available skills for autocomplete.
     */
    public function findAvailableSkillsForAdherent(string $term): array
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->where('s.name LIKE :name')
            ->setParameters([
                'name' => $term.'%',
            ])
        ;

        $skills = $qb->getQuery()->getArrayResult();

        foreach ($skills as $skill) {
            $names[] = $skill['name'];
        }

        return $names ?? [];
    }
}
