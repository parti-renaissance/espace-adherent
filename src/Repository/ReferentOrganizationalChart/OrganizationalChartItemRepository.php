<?php

namespace AppBundle\Repository\ReferentOrganizationalChart;

use AppBundle\Entity\ReferentOrganizationalChart\AbstractOrganizationalChartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class OrganizationalChartItemRepository extends NestedTreeRepository implements ServiceEntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManager $manager */
        $manager = $registry->getManagerForClass(AbstractOrganizationalChartItem::class);

        parent::__construct($manager, $manager->getClassMetadata(AbstractOrganizationalChartItem::class));
    }
}
