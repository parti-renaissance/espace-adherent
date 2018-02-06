<?php

namespace AppBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class RemoveCreateActionAdminExtension extends AbstractAdminExtension
{
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
        $collection->remove('create');
    }
}
