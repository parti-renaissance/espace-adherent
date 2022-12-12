<?php

namespace App\Admin;

use App\Entity\AdherentFormation\File;
use App\Form\Admin\BaseFileType;
use App\Form\PositionType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FormationAdmin extends AbstractAdmin
{
    protected function configureFormOptions(array &$formOptions): void
    {
        if ($this->isCurrentRoute('create')) {
            $formOptions['validation_groups'] = ['Default', 'adherent_formation_create'];
        }
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Méta-données', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                ])
            ->end()
            ->with('Visibilité', ['class' => 'col-md-6'])
                ->add('visible', CheckboxType::class, [
                    'label' => 'Visible',
                    'required' => false,
                ])
                ->add('zone', ModelAutocompleteType::class, [
                    'label' => 'Zone',
                    'property' => 'name',
                    'required' => false,
                    'help' => 'Laissez vide pour appliquer une visibilité nationale.',
                    'btn_add' => false,
                ])
                ->add('position', PositionType::class, [
                    'label' => 'Position sur la page',
                ])
            ->end()
            ->with('Fichier attaché', ['class' => 'col-md-6'])
                ->add('file', BaseFileType::class, [
                    'label' => false,
                    'required' => $this->isCurrentRoute('create'),
                    'data_class' => File::class,
                    'can_update_file' => true,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('visible', null, [
                'label' => 'Est visible ?',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('downloadsCount', null, [
                'label' => 'Téléchargements',
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('visible', null, [
                'label' => 'Visible',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
