<?php

namespace App\Repository\ReferentOrganizationalChart;

use App\Entity\ReferentOrganizationalChart\AbstractOrganizationalChartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class OrganizationalChartItemRepository extends NestedTreeRepository implements ServiceEntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass(AbstractOrganizationalChartItem::class);

        parent::__construct($manager, $manager->getClassMetadata(AbstractOrganizationalChartItem::class));
    }
}
