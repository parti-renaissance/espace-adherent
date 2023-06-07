<?php

namespace App\Admin;

use App\Entity\ReferentArea;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReferentAreaAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'areaCode';
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('areaCode', null, [
                'label' => 'Code',
            ])
            ->add('name', null, [
                'label' => 'Nom de la zone',
            ])
            ->add('areaTypeLabel', null, [
                'label' => 'Type de zone',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('areaCode', null, [
                'label' => 'Code',
            ])
            ->add('name', null, [
                'label' => 'Nom de la zone',
            ])
            ->add('areaTypeLabel', null, [
                'label' => 'Type de zone',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Informations générales', ['class' => 'col-md-6'])
                ->add('areaCode', TextType::class, [
                    'label' => 'Code',
                    'attr' => [
                        'placeholder' => '24 | 75002 | CH',
                        'maxlength' => '6',
                    ],
                ])
                ->add('areaType', ChoiceType::class, [
                    'label' => 'Type',
                    'choices' => ReferentArea::TYPE_CHOICES,
                    'expanded' => true,
                ])
                ->add('name', null, [
                    'label' => 'Nom de la zone',
                ])
            ->end()
            ->with('Mots-clés', ['class' => 'col-md-6'])
                ->add('keywords', CollectionType::class, [
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'by_reference' => false,
                    'entry_type' => TextType::class,
                    'label' => 'Mot-clés',
                ])
            ->end()
        ;
    }
}
