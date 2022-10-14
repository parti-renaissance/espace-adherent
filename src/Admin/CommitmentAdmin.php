<?php

namespace App\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommitmentAdmin extends AbstractAdmin
{
    protected function configureDefaultFilterValues(array &$filterValues)
    {
        $filterValues = array_merge($filterValues, [
            '_sort_order' => 'ASC',
            '_sort_by' => 'position',
        ]);
    }

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
                        'template' => '@PixSortableBehavior/Default/_sort_drag_drop.html.twig',
                        'enable_top_bottom_buttons' => true,
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
                ->add('position', IntegerType::class, [
                    'attr' => ['min' => 0],
                    'label' => 'Position',
                    'help' => 'Plus la position est élevée plus le block descendra sur la page.',
                ])
                ->add('shortDescription', null, ['label' => 'Description courte'])
                ->add('description', TextareaType::class, [
                    'label' => 'Description complète',
                    'attr' => ['class' => 'simplified-content-editor', 'rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                    'required' => false,
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

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
        $collection->add('move', $this->getRouterIdParameter().'/move/{position}');
    }
}
