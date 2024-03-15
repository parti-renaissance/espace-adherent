<?php

namespace App\Admin\Procuration;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
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
}
