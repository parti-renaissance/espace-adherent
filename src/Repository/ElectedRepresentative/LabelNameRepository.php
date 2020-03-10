<?php

namespace AppBundle\Repository\ElectedRepresentative;

use AppBundle\Entity\ElectedRepresentative\LabelName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LabelNameRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LabelName::class);
    }

    /**
     * Finds labels for autocomplete.
     */
    public function findForAutocomplete(string $term): array
    {
        $qb = $this->createQueryBuilder('label_name');
        $qb
            ->where('label_name.name LIKE :name')
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
