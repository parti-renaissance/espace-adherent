<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DistrictAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'filter_emojis' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('number', null, [
                'label' => 'Numéro',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('departmentCode', null, [
                'label' => 'Code du département',
            ])
            ->add('countries', null, [
                'label' => 'Pays',
            ])
            ->add('referentTag', null, [
                'label' => 'Tag référent',
            ])
        ;
    }
}
