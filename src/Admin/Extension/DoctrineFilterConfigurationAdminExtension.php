<?php

namespace AppBundle\Admin\Extension;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;

class DoctrineFilterConfigurationAdminExtension extends AbstractAdminExtension
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->em->getFilters()->disable('enabled');
    }
}
