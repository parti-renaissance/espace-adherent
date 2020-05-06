<?php

namespace App\Admin\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;

class DoctrineEnabledFilterConfigurationAdminExtension extends AbstractAdminExtension
{
    public function __construct(EntityManagerInterface $em)
    {
        $em->getFilters()->disable('enabled');
    }
}
