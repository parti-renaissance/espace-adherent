<?php

namespace App\Admin\Procuration;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProxyAdmin extends AbstractProcurationAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        parent::configureFormFields($form);

        $form
            ->with('Mandataire', ['class' => 'col-md-6'])
                ->add('electorNumber', TextType::class, [
                    'label' => 'Numéro d\'électeur',
                ])
                ->add('slots', ChoiceType::class, [
                    'label' => 'Votes disponibles',
                    'expanded' => true,
                    'choices' => [
                        '1' => 1,
                        '2' => 2,
                    ],
                ])
            ->end()
            ->with('Mandants', ['class' => 'col-md-6'])
                ->add('requests', ModelAutocompleteType::class, [
                    'label' => 'Mandants associés',
                    'required' => false,
                    'multiple' => true,
                    'minimum_input_length' => 2,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'btn_add' => false,
                    'by_reference' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        parent::configureDatagridFilters($filter);

        $filter
            ->add('electorNumber', null, [
                'label' => 'Numéro d\'électeur',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);

        $list
            ->add('slots', null, [
                'label' => 'Nombre de procuration',
            ])
            ->reorder([
                'id',
                '_fullName',
                'email',
                'phone',
                'voteZone',
                'slots',
                'createdAt',
                ListMapper::NAME_ACTIONS,
            ])
        ;
    }
}
