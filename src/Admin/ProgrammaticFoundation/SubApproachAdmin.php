<?php

namespace App\Admin\ProgrammaticFoundation;

use App\Entity\ProgrammaticFoundation\Approach;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SubApproachAdmin extends AbstractAdmin
{
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->addOrderBy('o.approach', 'ASC')
            ->addOrderBy('o.position', 'ASC')
        ;

        return $query;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('subtitle', null, [
                'label' => 'Sous-titre',
            ])
            ->add('approach', null, [
                'label' => 'Grand axe associé',
                'sortable' => true,
                'sort_parent_association_mappings' => [['fieldName' => 'approach']],
                'sort_field_mapping' => ['fieldName' => 'title'],
            ])
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
                'header_style' => 'width: 10%',
            ])
            ->add('isExpanded', null, [
                'label' => 'Ouvert par défaut',
                'header_style' => 'width: 10%',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'header_style' => 'width: 15%',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('approach', null, [
                'label' => 'Grand axe associé',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Axe secondaire')
                ->add('approach', EntityType::class, [
                    'label' => 'Grand axe associé',
                    'class' => Approach::class,
                    'placeholder' => 'Sélectionner un grand axe',
                ])
                ->add('title', null, [
                    'label' => 'Titre',
                ])
                ->add('subtitle', null, [
                    'label' => 'Sous-titre',
                ])
                ->add('content', TextareaType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                ])
                ->add('isExpanded', null, [
                    'label' => 'Ouvert par défaut',
                ])
                ->add('position', IntegerType::class, [
                    'label' => 'Ordre d\'affichage',
                    'scale' => 0,
                    'attr' => [
                        'min' => 1,
                    ],
                ])
            ->end()
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }
}
