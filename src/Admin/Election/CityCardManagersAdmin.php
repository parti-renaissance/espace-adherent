<?php

namespace App\Admin\Election;

use App\Form\Admin\Election\CityManagerType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class CityCardManagersAdmin extends AbstractCityCardAdmin
{
    protected $baseRouteName = 'admin_app_election_citycard_managers';
    protected $baseRoutePattern = 'app/election-citycard-managers';

    protected function configureFormFields(FormMapper $form)
    {
        parent::configureFormFields($form);

        $form
            ->add('headquartersManager', CityManagerType::class, [
                'label' => 'Siège',
                'required' => false,
            ])
            ->add('politicManager', CityManagerType::class, [
                'label' => 'Politique',
                'required' => false,
            ])
            ->add('taskForceManager', CityManagerType::class, [
                'label' => 'Task force',
                'required' => false,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list)
    {
        parent::configureListFields($list);

        $list
            ->add('headquartersManager', null, [
                'label' => 'Siège',
                'template' => 'admin/election/city_card/_list_manager.html.twig',
            ])
            ->add('politicManager', null, [
                'label' => 'Politique',
                'template' => 'admin/election/city_card/_list_manager.html.twig',
            ])
            ->add('taskForceManager', null, [
                'label' => 'Task force',
                'template' => 'admin/election/city_card/_list_manager.html.twig',
            ])
            ->reorder([
                'city.name',
                'city.inseeCode',
                'city.department',
                'city.department.region',
                'priority',
                'headquartersManager',
                'politicManager',
                'taskForceManager',
                '_action',
            ])
        ;
    }
}
