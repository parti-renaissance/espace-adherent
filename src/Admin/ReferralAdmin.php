<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class ReferralAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('identifier', null, [
                'label' => 'Numéro',
            ])
            ->add('referrer', null, [
                'label' => 'Adhérent',
                'template' => 'admin/referral/list_referrer.html.twig',
            ])
            ->add('emailAddress', null, [
                'label' => 'Email',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
        ;
    }
}
