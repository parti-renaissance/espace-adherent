<?php

declare(strict_types=1);

namespace App\Admin\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;

class DoctrineSoftdeleteableFilterConfigurationAdminExtension extends AbstractAdminExtension
{
    public function __construct(EntityManagerInterface $em)
    {
        $em->getFilters()->disable('softdeleteable');
    }
}
