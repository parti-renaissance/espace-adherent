<?php

namespace App\Admin;

use Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommitmentAdmin extends AbstractAdmin
{
    use SortableAdminTrait;

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('title', null, ['label' => 'Titre'])
            ->add('position', null, ['label' => 'Position'])
            ->add('createdAt', null, ['label' => 'Créée le'])
            ->add('updatedAt', null, ['label' => 'Modifiée le'])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                    'move' => [
                        'template' => '@RunroomSortableBehavior/sort.html.twig',
                    ],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('title', null, ['label' => 'Titre'])
                ->add('shortDescription', null, ['label' => 'Description courte'])
                ->add('description', TextareaType::class, [
                    'label' => 'Description complète',
                    'attr' => ['class' => 'simplified-content-editor', 'rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
            ->end()
            ->with('Photo', ['class' => 'col-md-6'])
                ->add('image', FileType::class, [
                    'label' => 'Ajoutez une photo',
                    'help' => 'La photo ne doit pas dépasser 5 Mo.',
                    'required' => $this->isCreation(),
                ])
            ->end()
        ;
    }
}
