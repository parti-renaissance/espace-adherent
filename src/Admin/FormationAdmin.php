<?php

namespace App\Admin;

use App\Entity\AdherentFormation\File;
use App\Form\Admin\BaseFileType;
use App\Form\PositionType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FormationAdmin extends AbstractAdmin
{
    use MediaSynchronisedAdminTrait;

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Méta-données', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'description',
                ])
                ->add('position', PositionType::class, [
                    'label' => 'Position sur la page',
                ])
            ->end()
            ->with('Fichier attaché', ['class' => 'col-md-12'])
                ->add('file', BaseFileType::class, [
                    'label' => false,
                    'attr' => ['accept' => 'application/pdf'],
                ], [
                    'data_class' => File::class,
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
