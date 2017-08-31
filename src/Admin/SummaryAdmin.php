<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;

class SummaryAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_par_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'id',
    ];

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('member', null, [
                'label' => 'Membre',
            ])
            ->add('currentProfession', null, [
                'label' => 'Métier principal',
            ])
            ->add('contributionWishLabel', null, [
                'label' => 'Souhait de contribution',
            ])
            ->add('availabilities', null, [
                'label' => 'Disponibilités',
                'template' => 'admin/summary/list_availabilities.html.twig',
            ])
            ->add('contactEmail', null, [
                'label' => 'Email',
            ])
            ->add('public', null, [
                'label' => 'Visible au public',
                'template' => 'admin/summary/public_show.html.twig',
            ])
        ;
    }
}
