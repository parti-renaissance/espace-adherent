<?php

namespace App\Repository\ReferentOrganizationalChart;

use App\Entity\ReferentOrganizationalChart\AbstractOrganizationalChartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class OrganizationalChartItemRepository extends NestedTreeRepository implements ServiceEntityRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        /** @var EntityManager $manager */
        $manager = $registry->getManagerForClass(AbstractOrganizationalChartItem::class);

        parent::__construct($manager, $manager->getClassMetadata(AbstractOrganizationalChartItem::class));
    }
}
