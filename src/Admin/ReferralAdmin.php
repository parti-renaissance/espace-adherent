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
            ->add('civility', null, [
                'label' => 'Civilité',
            ])
            ->add('_fullName', null, [
                'label' => 'Prénom/Nom',
                'virtual_field' => true,
                'template' => 'admin/referral/list_fullName.html.twig',
            ])
            ->add('_contact', null, [
                'label' => 'Email/Téléphone',
                'virtual_field' => true,
                'template' => 'admin/referral/list_contact.html.twig',
            ])
            ->add('referrer', null, [
                'label' => 'Parrain',
                'template' => 'admin/referral/list_referrer.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/referral/list_type.html.twig',
            ])
            ->add('mode', null, [
                'label' => 'Mode',
                'template' => 'admin/referral/list_mode.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/referral/list_status.html.twig',
            ])
        ;
    }
}
