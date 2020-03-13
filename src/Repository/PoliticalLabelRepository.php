<?php

namespace AppBundle\Repository;

use AppBundle\Entity\PoliticalLabel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PoliticalLabelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PoliticalLabel::class);
    }

    /**
     * Finds labels for autocomplete.
     */
    public function findForAutocomplete(string $term): array
    {
        $qb = $this->createQueryBuilder('political_label');
        $qb
            ->where('political_label.name LIKE :name')
            ->setParameters([
                'name' => $term.'%',
            ])
        ;

        $labels = $qb->getQuery()->getArrayResult();

        foreach ($labels as $label) {
            $names[] = $label['name'];
        }

        return $names ?? [];
    }
}
